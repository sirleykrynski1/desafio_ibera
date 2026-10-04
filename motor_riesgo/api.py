from fastapi import FastAPI
import pandas as pd

from riesgo import calcular_riesgo
from clima import obtener_lluvia_prevista

app = FastAPI(
    title="Motor de Riesgo Ambiental Iberá",
    version="1.0.0"
)

@app.get("/")
def inicio():
    return {
        "mensaje": "Motor de Riesgo Ambiental Iberá activo"
    }

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