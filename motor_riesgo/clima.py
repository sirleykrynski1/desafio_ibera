import requests

URL_CLIMA = "https://api.open-meteo.com/v1/forecast"

def obtener_lluvia_prevista(
    latitud: float,
    longitud: float
) -> float | None:
    parametros = {
        "latitude": latitud,
        "longitude": longitud,
        "daily": "precipitation_sum",
        "timezone": "America/Argentina/Buenos_Aires",
        "forecast_days": 3
    }
    try:
        respuesta = requests.get(
            URL_CLIMA,
            params=parametros,
            timeout=10
        )
        respuesta.raise_for_status()
        datos = respuesta.json()
        lluvias = datos["daily"]["precipitation_sum"]
        return sum(lluvias)

    except (
        requests.RequestException,
        KeyError,
        TypeError
    ) as error:
        print(f"Error al consultar el clima: {error}")
        return None
