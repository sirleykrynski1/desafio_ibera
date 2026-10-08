import json
import unittest
from unittest.mock import patch
from urllib.parse import urlencode

from api import app


class AlertaClimaticaTest(unittest.IsolatedAsyncioTestCase):
    async def consultar(self, parametros: dict, ruta: str = "/api/v1/alerta-climatica") -> tuple[int, dict]:
        """Ejecuta una solicitud ASGI real en memoria, sin servidor ni red."""
        mensajes = []

        async def recibir() -> dict:
            return {"type": "http.request", "body": b"", "more_body": False}

        async def enviar(mensaje: dict) -> None:
            mensajes.append(mensaje)

        await app({
            "type": "http", "asgi": {"version": "3.0"},
            "http_version": "1.1", "method": "GET", "scheme": "http",
            "path": ruta, "raw_path": ruta.encode(), "root_path": "",
            "query_string": urlencode(parametros).encode(), "headers": [],
            "client": ("127.0.0.1", 1234), "server": ("test", 80)
        }, recibir, enviar)
        estado = next(m["status"] for m in mensajes if m["type"] == "http.response.start")
        cuerpo = b"".join(m.get("body", b"") for m in mensajes if m["type"] == "http.response.body")
        return estado, json.loads(cuerpo)

    def setUp(self) -> None:
        consulta = patch("api.obtener_pronostico")
        self.pronostico = consulta.start()
        self.addCleanup(consulta.stop)
        self.datos = {
            "fuente": "open-meteo",
            "consultado_en": "2026-10-04T13:00:00+00:00",
            "zona_horaria": "America/Argentina/Buenos_Aires",
            "desde": "2026-10-04", "hasta": "2026-10-06",
            "precipitacion_acumulada_mm": 52.4,
            "dias": []
        }
        self.pronostico.return_value = self.datos

    async def test_devuelve_contrato_climatico_con_solo_coordenadas(self) -> None:
        estado, datos = await self.consultar({"latitud": -28.54, "longitud": -57.17})

        self.assertEqual(estado, 200)
        self.assertEqual(datos["estado"], "disponible")
        self.assertEqual(datos["ubicacion"], {"latitud": -28.54, "longitud": -57.17})
        self.assertEqual(datos["periodo"], {
            "desde": "2026-10-04", "hasta": "2026-10-06",
            "zona_horaria": "America/Argentina/Buenos_Aires"
        })
        self.assertEqual(datos["fuente"], "open-meteo")
        self.assertEqual(datos["consultado_en"], self.datos["consultado_en"])
        self.assertEqual(datos["version"], "1")
        self.assertEqual(datos["version_reglas"], "clima-v1")
        self.assertEqual(datos["precipitacion_acumulada_mm"], 52.4)
        self.assertTrue(datos["alerta"]["activa"])
        self.assertEqual(datos["alerta"]["codigo"], "PRECIPITACION_ACUMULADA_ALTA")
        self.assertTrue(datos["alerta"]["motivos"])
        self.pronostico.assert_called_once_with(-28.54, -57.17)

    async def test_activa_alerta_desde_el_umbral_inclusive(self) -> None:
        for total, activa in [(0, False), (39.9, False), (40, True), (40.1, True)]:
            with self.subTest(total=total):
                self.datos["precipitacion_acumulada_mm"] = total
                estado, datos = await self.consultar({"latitud": 0, "longitud": 0})
                self.assertEqual(estado, 200)
                self.assertEqual(datos["alerta"]["activa"], activa)
                if not activa:
                    self.assertIsNone(datos["alerta"]["codigo"])
                    self.assertEqual(datos["alerta"]["motivos"], [])

    async def test_falta_de_pronostico_devuelve_503_sin_inventar_lluvia(self) -> None:
        self.pronostico.return_value = None
        estado, datos = await self.consultar({"latitud": 0, "longitud": 0})

        self.assertEqual(estado, 503)
        self.assertEqual(datos["estado"], "no_disponible")
        self.assertEqual(datos["error"]["codigo"], "PRONOSTICO_NO_DISPONIBLE")
        self.assertNotIn("precipitacion_acumulada_mm", datos)
        self.assertNotIn("alerta", datos)

    async def test_rechaza_coordenadas_invalidas_antes_de_consultar_clima(self) -> None:
        casos = [{}, {"latitud": 0}, {"longitud": 0}]
        for nombre, valores in {
            "latitud": [-90.1, 90.1, "nan", "inf", "-inf", "texto"],
            "longitud": [-180.1, 180.1, "nan", "inf", "-inf", "texto"]
        }.items():
            for valor in valores:
                casos.append({"latitud": 0, "longitud": 0, nombre: valor})
        for parametros in casos:
            with self.subTest(parametros=parametros):
                estado, datos = await self.consultar(parametros)
                self.assertEqual(estado, 422)
                self.assertIn("detail", datos)
        self.pronostico.assert_not_called()

    async def test_acepta_coordenadas_en_los_limites(self) -> None:
        for latitud, longitud in [(-90, -180), (90, 180)]:
            with self.subTest(latitud=latitud, longitud=longitud):
                estado, _ = await self.consultar({"latitud": latitud, "longitud": longitud})
                self.assertEqual(estado, 200)

    async def test_ruta_anterior_conserva_calculo_de_riesgo(self) -> None:
        with patch("api.obtener_lluvia_prevista", return_value=50):
            estado, datos = await self.consultar({
                "hotel_id": 1, "hotel": "Hotel A", "latitud": -28.54,
                "longitud": -57.17, "huespedes": 20,
                "capacidad_maxima": 40, "dias_desde_mantenimiento": 30
            }, ruta="/riesgo")

        self.assertEqual(estado, 200)
        self.assertEqual(datos["nivel_riesgo"], "AMARILLO")
        self.assertEqual(datos["score_riesgo"], 3)
        self.assertEqual(datos["hotel_id"], 1)
        self.pronostico.assert_not_called()


if __name__ == "__main__":
    unittest.main()
