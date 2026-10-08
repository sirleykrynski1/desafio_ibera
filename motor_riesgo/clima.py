import logging
from datetime import date, datetime, timedelta, timezone
from math import isfinite
from typing import TypedDict

import requests

URL_CLIMA = "https://api.open-meteo.com/v1/forecast"
ZONA_HORARIA = "America/Argentina/Buenos_Aires"
DIAS_PRONOSTICO = 3
logger = logging.getLogger(__name__)


class PrecipitacionDiaria(TypedDict):
    fecha: str
    precipitacion_mm: float


class PronosticoClimatico(TypedDict):
    fuente: str
    consultado_en: str
    zona_horaria: str
    desde: str
    hasta: str
    precipitacion_acumulada_mm: float
    dias: list[PrecipitacionDiaria]

def obtener_pronostico(
    latitud: float,
    longitud: float
) -> PronosticoClimatico | None:
    """Obtiene tres días completos; None indica que no hay datos confiables."""
    parametros = {
        "latitude": latitud,
        "longitude": longitud,
        "daily": "precipitation_sum",
        "timezone": ZONA_HORARIA,
        "forecast_days": DIAS_PRONOSTICO,
        "precipitation_unit": "mm"
    }
    try:
        respuesta = requests.get(
            URL_CLIMA,
            params=parametros,
            timeout=10
        )
        respuesta.raise_for_status()
        datos = respuesta.json()
        fechas = datos["daily"]["time"]
        lluvias = datos["daily"]["precipitation_sum"]
        if (
            not isinstance(fechas, list)
            or not isinstance(lluvias, list)
            or len(fechas) != DIAS_PRONOSTICO
            or len(lluvias) != DIAS_PRONOSTICO
        ):
            raise ValueError("El pronóstico debe contener tres días completos.")

        if datos["daily_units"]["precipitation_sum"] != "mm":
            raise ValueError("La precipitación debe estar expresada en milímetros.")

        fechas_validas = [date.fromisoformat(fecha) for fecha in fechas]
        for indice, fecha in enumerate(fechas_validas):
            if fecha != fechas_validas[0] + timedelta(days=indice):
                raise ValueError("Las fechas deben ser consecutivas y ordenadas.")

        dias: list[PrecipitacionDiaria] = []
        for fecha, lluvia in zip(fechas_validas, lluvias):
            if (
                isinstance(lluvia, bool)
                or not isinstance(lluvia, (int, float))
                or not isfinite(lluvia)
                or lluvia < 0
            ):
                raise ValueError("La precipitación debe ser un número finito no negativo.")
            dias.append({
                "fecha": fecha.isoformat(),
                "precipitacion_mm": float(lluvia)
            })

        total = sum(dia["precipitacion_mm"] for dia in dias)
        if not isfinite(total):
            raise ValueError("La precipitación acumulada debe ser finita.")

        return {
            "fuente": "open-meteo",
            "consultado_en": datetime.now(timezone.utc).isoformat(),
            "zona_horaria": ZONA_HORARIA,
            "desde": dias[0]["fecha"],
            "hasta": dias[-1]["fecha"],
            "precipitacion_acumulada_mm": total,
            "dias": dias
        }

    except (
        requests.RequestException,
        KeyError,
        TypeError,
        ValueError,
        OverflowError
    ) as error:
        logger.warning("No se pudo obtener un pronóstico completo: %s", error)
        return None


def obtener_lluvia_prevista(
    latitud: float,
    longitud: float
) -> float | None:
    """Mantiene el resultado esperado por la API y el motor anteriores."""
    pronostico = obtener_pronostico(latitud, longitud)
    if pronostico is None:
        return None
    return pronostico["precipitacion_acumulada_mm"]
