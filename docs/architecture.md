# Arquitectura

WP Real Estate organiza el código en `includes/` bajo el namespace `WPRealEstate\`,
agrupado por responsabilidad en lugar de por tipo de artefacto:

- **Core** — arranque del plugin. `Loader` centraliza el registro de todos los
  `add_action`/`add_filter`; `Bootstrap` declara qué hooks usa cada componente
  y llama a `Loader::run()` una sola vez. `Activation`/`Deactivation` contienen
  las rutinas de (des)activación.
- **PostTypes** — registro de los CPT `wpre_listing` (Inmuebles) y `wpre_agent` (Agentes).
- **Taxonomies** — taxonomías asociadas a cada CPT.
- **Fields** — meta boxes y su lógica de guardado/sanitización, con un `schema()`
  declarativo que también consumen las vistas.
- **Admin** — menú, pestañas de ajustes (`Admin\Settings\*`) y columnas personalizadas
  de los listados.
- **DemoData** — generación de contenido de demostración (`DemoContentManager`,
  `AgentGenerator`, `ListingGenerator`) y el cliente de la API de Unsplash
  (`UnsplashClient`), ejecutado paso a paso vía AJAX desde Herramientas.
- **Support** — utilidades compartidas (formato de precios, catálogos de opciones).

`views/` contiene las plantillas PHP de los meta boxes; `assets/` los estilos y
scripts de administración, con el prefijo de clases/IDs `wpre-`.

## Convenciones de nombres

- Meta keys: `_wpre_*` (inmueble) y `_wpre_agent_*` (agente).
- Opciones: `wpre_*`.
- Nonces AJAX: acción `wpre_nonce`; endpoints `wpre_generate_demo` / `wpre_remove_demo`.
- Constantes globales: `WPRE_*`.
