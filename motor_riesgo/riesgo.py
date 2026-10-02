import pandas as pd

from config import (
    UMBRAL_OCUPACION_ALTA,
    UMBRAL_MANTENIMIENTO_ATRASADO,
    UMBRAL_LLUVIA_INTENSA,
    UMBRAL_OCUPACION_CRITICA,
    UMBRAL_MANTENIMIENTO_CRITICO,
    PESO_OCUPACION,
    PESO_MANTENIMIENTO,
    PESO_LLUVIA,
    MAX_VERDE,
    MAX_AMARILLO
)

def validar_datos(df: pd.DataFrame) -> None:
    columnas_requeridas = [
        "hotel_id",
        "hotel",
        "huespedes",
        "capacidad_maxima",
        "dias_desde_mantenimiento",
        "lluvias_previstas_mm",
    ]

    columnas_faltantes = [
        columna
        for columna in columnas_requeridas
        if columna not in df.columns
    ]

    if columnas_faltantes:
        raise ValueError(
            f"Faltan columnas requeridas: {columnas_requeridas}"
        )

    if (df["capacidad_maxima"] <= 0).any():
        raise ValueError(
            "La capacidad máxima debe ser mayor que 0."
        )

    if(df["huespedes"] < 0).any():
        raise ValueError(
            "La cantidad de huéspedes no puede ser negativa."
        )

    if(df["dias_desde_mantenimiento"] < 0).any():
        raise ValueError(
            "Los días desde el mantenimiento no pueden ser negativos."
        )

    if (
        df["huespedes"] > df["capacidad_maxima"]
    ).any():
        raise ValueError(
            "La cantidad de huéspedes no puede superar la capacidad máxima."
        )

def calcular_riesgo(df: pd.DataFrame) -> pd.DataFrame:
    validar_datos(df)

    df = df.copy()
    df["clima_disponible"] = df["lluvias_previstas_mm"].notna()

    df["ocupacion_pct"] = (
        df["huespedes"] / df["capacidad_maxima"]
    ) * 100

    df["score_riesgo"] = 0
    df.loc[
        df["ocupacion_pct"] >= UMBRAL_OCUPACION_ALTA, 
        "score_riesgo"
    ] += PESO_OCUPACION
    df.loc[
        df["dias_desde_mantenimiento"] > UMBRAL_MANTENIMIENTO_ATRASADO,
        "score_riesgo"
    ] += PESO_MANTENIMIENTO
    df.loc[
        df["lluvias_previstas_mm"] >= UMBRAL_LLUVIA_INTENSA,
        "score_riesgo"
    ] += PESO_LLUVIA

    df["nivel_riesgo"] = pd.cut(
        df["score_riesgo"],
        bins=[
            -1,
            MAX_VERDE,
            MAX_AMARILLO,
            float("inf")
        ],
        labels=[
            "VERDE",
            "AMARILLO",
            "ROJO"
        ]
    )

    df["nivel_riesgo"] = df["nivel_riesgo"].astype("object")

    condicion_critica = (
        (df["ocupacion_pct"] >= UMBRAL_OCUPACION_CRITICA) & (df["dias_desde_mantenimiento"] > UMBRAL_MANTENIMIENTO_CRITICO)
    )

    df.loc[
        condicion_critica,
        "nivel_riesgo"
    ] = "ROJO"

    df.loc[
        ~df["clima_disponible"],
        "nivel_riesgo"
    ] = "DATOS_INSUFICIENTES"

    df["motivos"] = df.apply(
        obtener_motivos,
        axis=1
    )

    df["recomendaciones"] = df.apply(
        obtener_recomendaciones,
        axis=1
    )

    df["accion_prioritaria"] = df.apply(
        obtener_accion_prioritaria,
        axis=1
    )

    return df

# Función: para obtener los motivos de riesgo de cada hotel
def obtener_motivos(fila):
    if not fila["clima_disponible"]:
        return [
            "No se pudo obtener el pronóstico meteorológico para el hotel, por lo que no se puede evaluar el riesgo de lluvias."
        ]
    motivos = []
    if fila["ocupacion_pct"] >= UMBRAL_OCUPACION_ALTA:
        motivos.append("Ocupación Alta")
    if fila["dias_desde_mantenimiento"] > UMBRAL_MANTENIMIENTO_ATRASADO:
        motivos.append("Mantenimiento Atrasado")
    if fila["lluvias_previstas_mm"] >= UMBRAL_LLUVIA_INTENSA:
        motivos.append("Lluvia Intensas Previstas")

    if not motivos:
        motivos.append("Sin factores críticos detectados")

    return motivos

# Función: para obtener las recomendaciones de riesgo de cada hotel
def obtener_recomendaciones(fila):
    if not fila["clima_disponible"]:
        return [
            "Reintentar la consulta meteorológica antes de evaluar el riesgo."
        ]
    recomendaciones = []

    if fila["ocupacion_pct"] >= UMBRAL_OCUPACION_ALTA:
        recomendaciones.append("Monitorear la capacidad Sanitaria debido a la alta ocupación.")
    if fila["dias_desde_mantenimiento"] > UMBRAL_MANTENIMIENTO_ATRASADO:
        recomendaciones.append("Programar mantenimiento o inspección del Sistema Sanitario.")
    if fila["lluvias_previstas_mm"] >= UMBRAL_LLUVIA_INTENSA:
        recomendaciones.append("Verificar preventivamente el sistema ante las lluvias intensas previstas.")

    if not recomendaciones:
        recomendaciones.append("Mantener el monitoreo y mantenimiento habitual.")

    return recomendaciones

# Función: para obtener la acción prioritaria de riesgo de cada hotel
def obtener_accion_prioritaria(fila):
    nivel = fila["nivel_riesgo"]

    if nivel == "DATOS_INSUFICIENTES":
        return (
            "Verificar disponibilidad de datos meteorológicos "
            "antes de emitir una evaluación de riesgo."
        )

    if nivel == "ROJO":
        return (
            "Priorizar una inspección preventiva del Sistema Sanitario "
            "y verificar su capacidad antes del evento de riesgo."
        )

    if nivel == "AMARILLO":
        return (
            "Programar una revisión preventiva y mantener monitoreo reforzado."
        )

    return(
        "Mantener el monitoreo y mantenimiento preventivo habitual."
    )