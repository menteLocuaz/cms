# Contrato de la API remota

Este front (CMS Builder PHP) consume una API REST en `${API_BASE_URL}`.
Todas las peticiones llevan `Authorization: Bearer ${API_TOKEN}` y esperan
`Content-Type: application/json`.

## Endpoints consumidos

> **Convención:** todos los endpoints reciben/responden JSON. Los cuerpos
> de filtros se serializan como query string (`?linkTo=…&equalTo=…`).

### Recursos

| Recurso | Métodos | Notas |
|---|---|---|
| `admins` | GET, POST, PUT, DELETE | Suffix `admin` para campos (`id_admin`, `token_admin`, etc.). Soporta `?login=true`. |
| `pages` | GET, POST, PUT, DELETE | Suffix `page`. |
| `modules` | GET, POST, PUT, DELETE | Suffix `module`. |
| `columns` | GET, POST, PUT, DELETE | Suffix `column`. |
| `folders` | GET, POST, PUT, DELETE | Suffix `folder`. Almacenes remotos. |
| `files` | GET, POST, PUT, DELETE | Suffix `file`. |
| `relations` | GET | JOIN genérico: `?rel=tables&type=table&linkTo=…&equalTo=…&select=…`. |

### Filtros y proyección

| Query param | Significado |
|---|---|
| `id` + `nameId` | Identificador para update/delete. |
| `linkTo` | Columna(s) a filtrar. |
| `equalTo` | Valor exacto (puede repetirse separando por coma para AND). |
| `search` | Búsqueda parcial sobre columnas text. |
| `between1` / `between2` | Rango sobre `linkTo`. |
| `orderBy` / `orderMode` | `ASC` \| `DESC`. |
| `startAt` / `endAt` | Paginación 0-based (OFFSET/LIMIT). |
| `select` | Proyección (columnas separadas por coma). |
| `token` | Token de admin (opcional según endpoint). |
| `table` + `suffix` | Metadata de seguridad. |
| `except` | Excluir campos de la actualización. |

### Formato de respuesta exitosa

```json
{
  "status": 200,
  "results": [ { "id_admin": 1, "email_admin": "…", "…": "…" } ],
  "total": 42
}
```

`total` solo aparece en listados paginados. `lastId` aparece en respuestas
`POST` para devolver el ID creado.

### Códigos de error

| `status` | Significado |
|---|---|
| `200` | OK (incluye "no encontrado" si la lista está vacía). |
| `401` | Token inválido o expirado. |
| `403` | Permisos insuficientes. |
| `404` | Recurso no encontrado. |
| `500` | Error interno. `results` contiene un mensaje legible. |

## Autenticación

- **Login** (`POST /admins?login=true&suffix=admin`): body
  `{ email_admin, password_admin }`. La API responde con el admin completo;
  el password se almacena hasheado con `password_hash(PASSWORD_BCRYPT)`.
- **Acciones autenticadas** (`POST/PUT/DELETE`): requieren
  `?token=…&table=admins&suffix=admin` en query string. El token se obtiene
  del campo `token_admin` tras login.
- **Endpoints públicos** (registro inicial, instalación): usan
  `?token=no&except=…` para indicar que se omite la verificación.

## Endpoints especiales

- `POST /admins?register=true&suffix=admin` — registro sin token (usado por
  el instalador).
- `POST /admins?login=true&suffix=admin` — login.
- `GET /relations?rel=modules,pages&type=module,page&linkTo=…` — JOIN
  genérico entre dos tablas usando `type` para los sufijos.
