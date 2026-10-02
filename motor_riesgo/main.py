import pandas as pd
from riesgo import calcular_riesgo
from clima import obtener_lluvia_prevista
import json

hoteles = pd.DataFrame ({
    "hotel_id": [1, 2, 3],
    "hotel": [
        "Hotel A",
        "Hotal B",
        "Hotel C"
    ],
    "latitud": [
        -28.54,
        -28.55,
        -28.53
    ],
    "longitud" : [
        -57.17,
        -57.16,
        -57.18
    ],
    "huespedes": [20, 45, 10],
    "capacidad_maxima": [40, 50, 30],
    "dias_desde_mantenimiento": [30, 150, 70],
})

# Calcular el riesgo para cada hotel
hoteles["lluvias_previstas_mm"] = hoteles.apply(
    lambda fila: obtener_lluvia_prevista(
        fila["latitud"],
        fila["longitud"]
    ),
    axis=1
)

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

salida = resultado[columnas_salida].to_dict(
    orient="records"
)

json_salida = json.dumps(
    salida,
    ensure_ascii=False,
    indent=4
)

print(json_salida)