import unittest
from copy import deepcopy
from datetime import datetime
from unittest.mock import Mock, patch

import requests

from clima import obtener_lluvia_prevista, obtener_pronostico


class PronosticoTest(unittest.TestCase):
    def setUp(self) -> None:
        self.datos = {
            "daily_units": {"precipitation_sum": "mm"},
            "daily": {
                "time": ["2026-10-04", "2026-10-05", "2026-10-06"],
                "precipitation_sum": [10, 20.5, 0]
            }
        }
        self.consulta = patch("clima.requests.get").start()
        self.addCleanup(patch.stopall)
        self.respuesta = Mock()
        self.respuesta.json.return_value = self.datos
        self.consulta.return_value = self.respuesta
        patch("clima.logger").start()

    def test_conserva_fechas_datos_diarios_y_total(self) -> None:
        resultado = obtener_pronostico(-28.54, -57.17)

        self.assertIsNotNone(resultado)
        self.assertEqual(resultado["precipitacion_acumulada_mm"], 30.5)
        self.assertEqual(resultado["desde"], "2026-10-04")
        self.assertEqual(resultado["hasta"], "2026-10-06")
        self.assertEqual(resultado["dias"][1], {
            "fecha": "2026-10-05", "precipitacion_mm": 20.5
        })
        self.assertIsNotNone(datetime.fromisoformat(resultado["consultado_en"]).tzinfo)
        self.assertEqual(self.consulta.call_args.kwargs["params"]["latitude"], -28.54)
        self.assertEqual(self.consulta.call_args.kwargs["params"]["precipitation_unit"], "mm")
        self.assertEqual(self.consulta.call_args.kwargs["timeout"], 10)

    def test_cero_es_un_pronostico_valido(self) -> None:
        self.datos["daily"]["precipitation_sum"] = [0, 0, 0]

        self.assertEqual(obtener_pronostico(-28.54, -57.17)["precipitacion_acumulada_mm"], 0)

    def test_rechaza_precipitaciones_incompletas_o_invalidas(self) -> None:
        casos = [[], [1, 2], [1, 2, 3, 4], None, [1, None, 3],
                 [1, -1, 3], [1, "2", 3], [1, True, 3],
                 [1, float("nan"), 3], [1, float("inf"), 3],
                 [1e308, 1e308, 1e308]]
        for valores in casos:
            with self.subTest(valores=valores):
                self.datos["daily"]["precipitation_sum"] = valores
                self.assertIsNone(obtener_pronostico(-28.54, -57.17))

    def test_rechaza_fechas_incompletas_invalidas_o_desordenadas(self) -> None:
        casos = [[], ["2026-10-04"], None,
                 ["invalida", "2026-10-05", "2026-10-06"],
                 [None, "2026-10-05", "2026-10-06"],
                 ["2026-10-04", "2026-10-04", "2026-10-06"],
                 ["2026-10-06", "2026-10-05", "2026-10-04"],
                 ["2026-10-04", "2026-10-06", "2026-10-07"]]
        for fechas in casos:
            with self.subTest(fechas=fechas):
                self.datos["daily"]["time"] = fechas
                self.assertIsNone(obtener_pronostico(-28.54, -57.17))

    def test_rechaza_estructura_incorrecta_y_unidades_distintas(self) -> None:
        unidad_incorrecta = deepcopy(self.datos)
        unidad_incorrecta["daily_units"]["precipitation_sum"] = "inch"
        for datos in [None, [], {}, {"daily": {}}, unidad_incorrecta]:
            with self.subTest(datos=datos):
                self.respuesta.json.return_value = datos
                self.assertIsNone(obtener_pronostico(-28.54, -57.17))

    def test_maneja_json_invalido(self) -> None:
        self.respuesta.json.side_effect = ValueError("JSON inválido")

        self.assertIsNone(obtener_pronostico(-28.54, -57.17))

    def test_maneja_timeout_y_fallo_de_conexion(self) -> None:
        for error in [requests.Timeout(), requests.ConnectionError()]:
            with self.subTest(error=error):
                self.consulta.side_effect = error
                self.assertIsNone(obtener_pronostico(-28.54, -57.17))

    def test_maneja_error_http(self) -> None:
        self.respuesta.raise_for_status.side_effect = requests.HTTPError()

        self.assertIsNone(obtener_pronostico(-28.54, -57.17))
        self.respuesta.json.assert_not_called()

    def test_funcion_anterior_sigue_devolviendo_total(self) -> None:
        self.assertEqual(obtener_lluvia_prevista(-28.54, -57.17), 30.5)

    def test_funcion_anterior_distingue_cero_de_datos_faltantes(self) -> None:
        self.datos["daily"]["precipitation_sum"] = [0, 0, 0]
        self.assertEqual(obtener_lluvia_prevista(-28.54, -57.17), 0)

        self.datos["daily"]["precipitation_sum"] = []
        self.assertIsNone(obtener_lluvia_prevista(-28.54, -57.17))


if __name__ == "__main__":
    unittest.main()
