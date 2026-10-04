from typing import Annotated

from fastapi import FastAPI, Query
from fastapi.responses import JSONResponse
import pandas as pd

from riesgo import calcular_riesgo
from clima import obtener_lluvia_prevista, obtener_pronostico
from config import UMBRAL_PRECIPITACION_ACUMULADA_MM, VERSION_REGLAS_CLIMA

app = FastAPI(
    title="Motor de Riesgo Ambiental Iberá",
    version="1.0.0"
)

@app.get("/")
def inicio():
    return {
        "mensaje": "Motor de Riesgo Ambiental Iberá activo"
    }

@app.get("/api/v1/alerta-climatica")
def obtener_alerta_climatica(
    latitud: Annotated[float, Query(ge=-90, le=90, allow_inf_nan=False)],
    longitud: Annotated[float, Query(ge=-180, le=180, allow_inf_nan=False)]
) -> JSONResponse:
    """Evalúa precipitación acumulada; no determina cumplimiento de efluentes."""
    pronostico = obtener_pronostico(latitud, longitud)
    if pronostico is None:
        return JSONResponse(status_code=503, content={
            "version": "1",
            "estado": "no_disponible",
            "error": {
                "codigo": "PRONOSTICO_NO_DISPONIBLE",
                "mensaje": "No se pudo obtener un pronóstico completo."
            }
        })

    alerta_activa = (
        pronostico["precipitacion_acumulada_mm"]
        >= UMBRAL_PRECIPITACION_ACUMULADA_MM
    )
    motivos = []
    if alerta_activa:
        motivos.append(
            "La precipitación acumulada prevista alcanza o supera "
            f"el umbral preventivo de {UMBRAL_PRECIPITACION_ACUMULADA_MM} mm "
            "en tres días."
        )

    return JSONResponse(content={
        "version": "1",
        "estado": "disponible",
        "ubicacion": {"latitud": latitud, "longitud": longitud},
        "fuente": pronostico["fuente"],
        "consultado_en": pronostico["consultado_en"],
        "periodo": {
            "desde": pronostico["desde"],
            "hasta": pronostico["hasta"],
            "zona_horaria": pronostico["zona_horaria"]
        },
        "precipitacion_acumulada_mm": pronostico["precipitacion_acumulada_mm"],
        "alerta": {
            "activa": alerta_activa,
            "codigo": "PRECIPITACION_ACUMULADA_ALTA" if alerta_activa else None,
            "motivos": motivos
        },
        "version_reglas": VERSION_REGLAS_CLIMA
    })


@app.get("/riesgo")
def obtener_riesgo(
    hotel_id: int,
    hotel: str,
    latitud: float,
    longitud: float,
    huespedes: int,
    capacidad_maxima: int,
    dias_desde_mantenimiento: int
):
    lluvia = obtener_lluvia_prevista(latitud, longitud)
    hoteles = pd.DataFrame([
        {
            "hotel_id": hotel_id,
            "hotel": hotel,
            "huespedes": huespedes,
            "capacidad_maxima": capacidad_maxima,
            "dias_desde_mantenimiento": dias_desde_mantenimiento,
            "lluvias_previstas_mm": lluvia
        }
    ])

    resultado = calcular_riesgo(hoteles)

    columnas_salida = [
        "hotel_id",
        "hotel",
        "ocupacion_pct",
        "lluvias_previstas_mm",
        "score_riesgo",
        "nivel_riesgo",
        "motivos",
        "accion_prioritaria",
        "recomendaciones"
    ]

    salida = resultado[
        columnas_salida
    ].to_dict(
        orient="records"
    )

    return salida[0]
