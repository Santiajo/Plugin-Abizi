<?php

/**
 * Plugin Name: WooCommerce MercadoLibre Sync
 * Description: Sincroniza productos entre Mercadolibre y WooCommerce
 * Version: 14.04.25
 * Author: Equipo Plugin Mercadolibre
 * Text Domain: woo-ml-sync
 * Domain Path: /languages
 */

// Evita el acceso directo al archivo
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('WOO_ML_API_ENDPOINT', 'https://api.mercadolibre.com');
define('WOO_ML_MIN_PRICE', 1100);

class WooMercadoLibreSync
{
    const TEXT_DOMAIN = 'woo-ml-sync';

    //Declara variables
    private $access_token;
    private $refresh_token;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $debug_messages = array();


// === FUNCIONES RELACIONADAS CON WOOCOMMERCE ===
    public function __construct()
    {
        //Al activar el plugin, crea las options de los atributos
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        //Al cargar Wordpress, le asigna como valor a los atributos las options almacenadas
        add_action('init', array($this, 'load_settings'));
        //Al crear el menu lateral, ejecuta la función admin_page
        add_action('admin_menu', array($this, 'add_admin_menu'));
        //Se activa cuando se inicializa una pantalla de administración o un script.
        //Guarda en options, las variables client_id y client_secret
        add_action('admin_init', array($this, 'register_settings'));
        //Autentificación de Mercadolibre
        add_action('admin_init', array($this, 'handle_oauth_response'));
        // Pruebas Mauricio
        add_action('wp_ajax_importar_sku_directo', [$this, 'importar_sku_directo']);
        add_action('wp_ajax_get_lista_ids', array($this, 'get_lista_ids'));
        add_action('wp_ajax_importar_producto_id', array($this, 'importar_producto_id'));



        //Sincroniza stock de Mercadolibre a WooCommerce
        //$this->init_internal_api();
        //Sincroniza stock de WooCommerce a Mercadolibre en productos simples
        add_action('woocommerce_product_set_stock', array($this, 'sync_stock_to_mercadolibre'), 10, 1);
        //Sincroniza stock de WooCommerce a Mercadolibre en productos variables
        add_action('woocommerce_variation_set_stock', array($this, 'sync_variation_stock_to_mercadolibre'), 10, 2);

        //Herramientas Seller Custom Field
        add_action('wp_ajax_verificar_seller_custom_field', array($this, 'verificar_seller_custom_field'));
        add_action('wp_ajax_asignar_seller_custom_field', array($this, 'asignar_seller_custom_field'));

        //Herramientas SKU SKU
        add_action('wp_ajax_verificar_sku', array($this, 'verificar_sku'));
        add_action('wp_ajax_asignar_sku', array($this, 'asignar_sku'));

        //Solicitudes AJAX al pulsar un botón en la vista
        add_action('wp_ajax_importarTodoMercadolibre', array($this, 'importarTodoMercadolibre'));
        add_action('wp_ajax_sync_all_products', array($this, 'sync_all_products'));

        //Herramientas
        add_action('wp_ajax_borrarProductosWooCommerce', array($this, 'borrarProductosWooCommerce'));
        add_action('wp_ajax_upload_product_to_mercadolibre', array($this, 'upload_product_to_mercadolibre'));
        add_action('wp_ajax_verificarSKU', array($this, 'verificarSKU'));

        //Importacion Masiva
        add_action('wp_ajax_crearSku', array($this, 'crearSku'));
        add_action('wp_ajax_importar_pijamas', array($this, 'importar_pijamas'));

        //Importacion por categorias
        add_action('wp_ajax_return_sku', array($this, 'return_sku'));

        // add_action('wp_ajax_testing', array($this, 'testt'));
        
        //Nuevas Funciones
        add_action('wp_ajax_woo_ml_buscar_item_por_id', [$this, 'buscarporid']);
        add_action('wp_ajax_tomaridcategorias_ajax', [$this, 'tomaridcategorias']);
        add_action('wp_ajax_nopriv_tomaridcategorias_ajax', [$this, 'tomaridcategorias']); // si quieres acceso público (opcional)
        
        //frontend
        add_action('wp_ajax_woo_ml_importar_categoria_dinamica', [$this, 'asignar_seller_custom_field_individual']);
        add_action('wp_ajax_importararchivo', array($this, 'importarProductosDesdeArchivo'));

        add_filter('woo_multisite_stock_sync' . '_product_invalid_meta_keys', function ($invalid_meta_keys) {
            $invalid_meta_keys[] = '_mercadolibre_id';
            return $invalid_meta_keys;
        });
        
        /* Funciones hechas por (Freddy, Carlos, Pato y Esteban) [23-08-25] */

        // Función para cargar los productos en el fronted [Nav->#productos]
        add_action('wp_ajax_get_synced_products', array($this, 'get_synced_products_callback'));
        
    }

    

// === FUNCIONES RELACIONADAS CON MERCADO LIBRE ===
    //testing
    
//     public function testt()
//   {
    
//     // ID de categoría a consultar
//     $categoria_id = 'MLC363814';

//     // Obtener token y datos desde opciones guardadas en WordPress
//     $access_token = get_option('woo_ml_access_token');
//     $refresh_token = get_option('woo_ml_refresh_token');
//     $expiration = get_option('woo_ml_token_expiration');


//     // Si el token expiró, intentamos refrescarlo
//     if (time() >= $expiration) {
//         $this->log_debug("♻️ Token expirado. Intentando refrescar con refresh_token: {$refresh_token}");

//         $new_token_data = $this->refresh_access_token($refresh_token);

//         if ($new_token_data && isset($new_token_data['access_token'])) {
//             $access_token = $new_token_data['access_token'];
//             $this->log_debug("✅ Token refrescado exitosamente: {$access_token}");
//         } else {
//             $this->log_error("❌ No se pudo refrescar el token.");
//             return;
//         }
//     }

   
    

//     // Realizar solicitud a la API de categorías
//     $response = wp_remote_get("https://api.mercadolibre.com/categories/{$categoria_id}", [
//         'headers' => [
//             'Authorization' => 'Bearer ' . $access_token,
//         ]
//     ]);

//     // Validar respuesta
//     if (is_wp_error($response)) {
//         $this->log_error("❌ Error HTTP al obtener categoría {$categoria_id}: " . $response->get_error_message());
//         return;
//     }

//     // Procesar datos
//     $data = json_decode(wp_remote_retrieve_body($response), true);
 

//     // Extraer jerarquía de categoría
//     $jerarquia = [];

//     if (!empty($data['path_from_root'])) {
//         foreach ($data['path_from_root'] as $item) {
//             $jerarquia[] = $item['name'];
//         }
//     } else {
//         $this->log_error("⚠️ La categoría {$categoria_id} no tiene jerarquía 'path_from_root'.");
//     }

//     $this->log_debug("=============================================================");
//     // Formatear resultado
//     $categoria_completa = implode(' > ', $jerarquia);
//     $nombre_categoria = $data['name'] ?? 'Nombre no disponible';
//     $resultado = "{$categoria_id} | Categoría: {$nombre_categoria} | ID: {$categoria_id}\nCategoría completa: {$categoria_completa}";


//     // Registrar resultado final
    
   
//     $this->log_debug($resultado);
//     $this->log_debug("=============================================================");
   
//     }
    
    
    //--------------------------------------------------------------------------------------------------------------------------------------------------------------
    
    
    
    
    //  public function testing() 
    // {
    // // Definir seller_id y access_token como constantes
    // define('SELLER_ID', '1720493754'); // Aquí va el seller_id fijo
    // $access_token = get_option('woo_ml_access_token');
    // $refresh_token = get_option('woo_ml_refresh_token');
    // $expiration = get_option('woo_ml_token_expiration');

    // // Si el token expiró, intentar refrescarlo
    // if (time() >= $expiration) {
    //     $new_token_data = $this->refresh_access_token($refresh_token);
    //     if ($new_token_data && isset($new_token_data['access_token'])) {
    //         $access_token = $new_token_data['access_token']; // actualizamos el valor
    //     } else {
    //         error_log('No se pudo refrescar el token.');
    //         return;
    //     }
    // }

    // $producto_id = 'MLC1472079019';

    // // Realizar la solicitud a la API de MercadoLibre
    // $response_producto = wp_remote_get("https://api.mercadolibre.com/items/{$producto_id}", [
    //     'headers' => [
    //         'Authorization' => 'Bearer ' . $access_token,
    //     ]
    // ]);

    //     // Verificar si hay errores en la respuesta
    //     if (is_wp_error($response_producto)) {
    //         wp_send_json_error(['mensaje' => '❌ Error al hacer la solicitud: ' . $response_producto->get_error_message()]);
    //         return;
    //     }

    //     // Obtener los datos del producto
    //     $producto_data = json_decode(wp_remote_retrieve_body($response_producto), true);

    //     // Mostrar la respuesta completa para depuración
    //     $this->log_debug("Respuesta de la API: " . json_encode($producto_data));

    //     // Verifica el seller_id del producto
    //     if (isset($producto_data['seller_id'])) {
    //         wp_send_json_success([
    //             'mensaje' => '✅ Seller ID del producto: ' . $producto_data['seller_id'],
    //             'producto_id' => $producto_id,
    //             'usuario' => SELLER_ID
    //         ]);
    //     } else {
    //         wp_send_json_error([
    //             'mensaje' => '❌ Error al obtener el seller_id del producto',
    //             'producto_id' => $producto_id
    //         ]);
    //     }
    // }

   public function importar_producto_especifico($producto_id)
    {
        try {
            // Bloqueo para evitar ejecuciones duplicadas
            if (get_transient("importando_{$producto_id}")) {
                return ['success' => false, 'mensaje' => '⏳ Ya se está importando este producto.'];
            }
            set_transient("importando_{$producto_id}", true, 60); // Bloquea por 60 segundos
    
            // Refrescar token si es necesario
            if (!$this->check_and_refresh_token()) {
                throw new Exception('❌ No se pudo refrescar el token de acceso.');
            }
    
            // Obtener los datos del producto desde Mercado Libre
            $response_producto = $this->request_with_retries("https://api.mercadolibre.com/items/{$producto_id}", [
                'Authorization' => 'Bearer ' . $this->access_token
            ]);
    
            $producto_data = json_decode(wp_remote_retrieve_body($response_producto), true);
            if ($producto_data === null || json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar la respuesta JSON de Mercado Libre.');
            }
    
            $sku = $producto_data['seller_custom_field'] ?? $producto_id;
            $this->log_debug("SKU detectado o asignado para la búsqueda: {$sku}");
    
            // Buscar si el producto ya existe en WooCommerce por su SKU (que ahora será el ID de ML)
            $wc_product_id = wc_get_product_id_by_sku($sku);
    
            // Si el producto ya existe, se actualiza. Si no, se crea desde cero.
            if ($wc_product_id) {
                $this->log_debug("Producto encontrado en WooCommerce con ID: {$wc_product_id}. Actualizando...");
                // Lógica de actualización
                $resultado = $this->importarProductoDesdeMercadolibre($producto_id);
            } else {
                $this->log_debug("Producto no encontrado en WooCommerce. Creando uno nuevo...");
                // Lógica de creación
                $resultado = $this->importarProductoDesdeMercadolibre($producto_id);
            }
            
            delete_transient("importando_{$producto_id}"); // Liberar bloqueo
    
            if (is_array($resultado) && !empty($resultado['wc_product_id'])) {
                return [
                    'success' => true,
                    'mensaje' => 'Producto procesado correctamente.',
                    'producto_data' => $producto_data
                ];
            } else {
                throw new Exception('Error durante el proceso de creación/actualización en WooCommerce.');
            }
    
        } catch (Exception $e) {
            delete_transient("importando_{$producto_id}"); // Asegurarse de liberar el bloqueo en caso de error
            $this->log_error("⛔ Error al importar producto {$producto_id}: " . $e->getMessage());
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function importar_sku_directo()
    {
        // Verificación de seguridad
        if (!isset($_POST['id_ml']) || !wp_verify_nonce($_POST['nonce'], 'importar_producto_individual_nonce')) {
            wp_send_json_error(['mensaje' => '❌ Error de seguridad o ID no proporcionado.']);
            return;
        }

        // Sanitiza el ID de Mercado Libre recibido
        $producto_id_ml = sanitize_text_field($_POST['id_ml']);

        if (empty($producto_id_ml)) {
            wp_send_json_error(['mensaje' => '❌ El ID de Mercado Libre no puede estar vacío.']);
            return;
        }

        $this->log_debug("Iniciando importación individual para el ID de ML: {$producto_id_ml}");

        try {
            // Llama a la función principal que ya contiene toda la lógica de importación
            // Esta función ya maneja la creación, actualización, token, etc.
            $resultado = $this->importar_producto_especifico($producto_id_ml);

            // Analiza el resultado devuelto por la función de importación
            if (isset($resultado['success']) && $resultado['success']) {
                // Si la importación fue exitosa
                $mensaje_exito = "✅ Producto ID {$producto_id_ml} importado/actualizado correctamente. " . ($resultado['mensaje'] ?? '');
                $this->log_debug($mensaje_exito);
                wp_send_json_success(['mensaje' => $mensaje_exito]);
            } else {
                // Si hubo un fallo durante la importación
                $mensaje_error = "⚠️ Fallo al importar producto ID {$producto_id_ml}. Razón: " . ($resultado['mensaje'] ?? 'Error desconocido.');
                $this->log_error($mensaje_error);
                wp_send_json_error(['mensaje' => $mensaje_error]);
            }
        } catch (Exception $e) {
            // Captura cualquier error inesperado durante el proceso
            $this->log_error("Excepción crítica al importar {$producto_id_ml}: " . $e->getMessage());
            wp_send_json_error(['mensaje' => '❌ Ocurrió un error crítico en el servidor.']);
        }
    }

    
    public function asignar_seller_custom_field_individual()
    {
        $SELLER_ID = '1720493754';
        
        $categoria_recibida = isset($_POST['categoria_id']) ? sanitize_text_field($_POST['categoria_id']) : null;

        if (!$categoria_recibida) {
            wp_send_json_error(['mensaje' => '❌ No se recibió una categoría.']);
            return;
        }
        
        $CATEGORIAS_OBJETIVO = [$categoria_recibida];

        $access_token = get_option('woo_ml_access_token');
        $refresh_token = get_option('woo_ml_refresh_token');
        $expiration = get_option('woo_ml_token_expiration');

        if (time() >= $expiration) {
            $new_token_data = $this->refresh_access_token($refresh_token);
            if ($new_token_data && isset($new_token_data['access_token'])) {
                $access_token = $new_token_data['access_token'];
            } else {
                $this->log_error("❌ No se pudo refrescar el token.");
                wp_send_json_error(['mensaje' => '❌ No se pudo refrescar el token.']);
                return;
            }
        }

        $productos_ids = [];
        $scroll_id = null;
        $limit = 50;

        do {
            $endpoint = "https://api.mercadolibre.com/users/{$SELLER_ID}/items/search?search_type=scan&status=active&limit={$limit}";
            if ($scroll_id) {
                $endpoint .= "&scroll_id={$scroll_id}";
            }

            $response = wp_remote_get($endpoint, [
                'headers' => ['Authorization' => 'Bearer ' . $access_token],
                'timeout' => 20
            ]);

            if (is_wp_error($response)) {
                $this->log_error("❌ Error en scroll de productos: " . $response->get_error_message());
                break;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (empty($data['results'])) break;

            foreach ($data['results'] as $id) {
                $productos_ids[] = $id;
            }

            $scroll_id = $data['scroll_id'] ?? null;
        } while (!empty($scroll_id));

        $productos_filtrados = [];
        $lote = array_chunk($productos_ids, 20);

        foreach ($lote as $batch) {
            try {
                $ids_param = implode(",", $batch);
                $items_url = "https://api.mercadolibre.com/items?ids=" . $ids_param;

                $res = wp_remote_get($items_url, [
                    'headers' => ['Authorization' => 'Bearer ' . $access_token],
                    'timeout' => 20
                ]);

                if (is_wp_error($res)) throw new Exception("Timeout al cargar lote.");

                $items_data = json_decode(wp_remote_retrieve_body($res), true);
                foreach ($items_data as $item_obj) {
                    $item = $item_obj['body'] ?? null;
                    if ($item && isset($item['category_id']) && in_array($item['category_id'], $CATEGORIAS_OBJETIVO)) {
                        $productos_filtrados[] = $item['id'];
                    }
                }
            } catch (Exception $e) {
                $this->log_error("⛔ Lote fallido: " . $e->getMessage());
                continue;
            }
        }

        $resultados = [];

        foreach ($productos_filtrados as $producto_id) {
            $sku_actual = null;
            $producto_data = null;
            try {
                $producto_response = wp_remote_get("https://api.mercadolibre.com/items/{$producto_id}", [
                    'headers' => ['Authorization' => 'Bearer ' . $access_token],
                    'timeout' => 20
                ]);

                if (is_wp_error($producto_response)) {
                    throw new Exception("Timeout al traer producto: " . $producto_response->get_error_message());
                }

                $status_code = wp_remote_retrieve_response_code($producto_response);
                if ($status_code !== 200) {
                    throw new Exception("Código HTTP inesperado: {$status_code}");
                }

                $producto_data = json_decode(wp_remote_retrieve_body($producto_response), true);
                $sku_actual = $producto_data['seller_custom_field'] ?? null;

                if (empty($sku_actual)) {
                    $this->log_debug("➡️ Producto {$producto_id} sin SKU. Intentando asignar...");
                    $resultado_sku = $this->crear_sku($producto_id);
                    if (!$resultado_sku) {
                        throw new Exception("❌ No se pudo crear SKU a {$producto_id}.");
                    }
                    $sku_estado = '✅ SKU creado';
                } else {
                    $sku_estado = '🟢 SKU ya asignado';
                }

                // IMPORTAR PRODUCTO
                $resultado_importacion = $this->importar_producto_especifico($producto_id);
                $importado = $resultado_importacion['success'] ?? false;
                $mensaje_importacion = $resultado_importacion['mensaje'] ?? 'Sin mensaje';

                $resultados[] = [
                    'producto_id' => $producto_id,
                    'sku' => $producto_data['seller_custom_field'] ?? 'Asignado',
                    'title' => $producto_data['title'] ?? 'Sin título',
                    'estado' => "{$sku_estado} | " . ($importado ? '✅ Importado' : '❌ No importado'),
                    'detalle' => $mensaje_importacion
                ];
            } catch (Exception $e) {
                $this->log_error("⛔ Error con {$producto_id}: " . $e->getMessage());
                $resultados[] = [
                    'producto_id' => $producto_id,
                    'sku' => $sku_actual ?? 'Desconocido',
                    'title' => $producto_data['title'] ?? 'Sin título',
                    'estado' => '❌ Error',
                    'detalle' => $e->getMessage()
                ];
                continue;
            }
        }

        wp_send_json_success(['resultados' => $resultados]);
    }

    private function request_with_retries($url, $headers, $max_retries = 3, $timeout = 20) {
        for ($i = 0; $i < $max_retries; $i++) {
            $response = wp_remote_get($url, [
                'headers' => $headers,
                'timeout' => $timeout
            ]);

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                return $response;
            }

            sleep(1);
        }

        throw new Exception("❌ Fallo al obtener respuesta después de {$max_retries} intentos.");
    }

    public function buscarporid() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woo_ml_search_nonce')) {
            wp_send_json_error(['mensaje' => '❌ Fallo en la verificación de seguridad. Por favor, recarga la página y vuelve a intentarlo.']);
            return;
        }

        $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    
        if (empty($item_id)) {
            wp_send_json_error(['mensaje' => '❌ Debes proporcionar un ID de producto.']);
            return;
        }
    
        if (!$this->check_and_refresh_token()) {
            wp_send_json_error(['mensaje' => '❌ No se pudo refrescar el token.']);
            return;
        }
    
        $url = WOO_ML_API_ENDPOINT . "/items/{$item_id}";
        $response = $this->make_api_request_sinLog($url, 'GET');
        $http_status = wp_remote_retrieve_response_code($response);
    
        if ($http_status == 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            $info_relevante = [
                'Título' => $data['title'] ?? 'N/A',
                'ID de Publicación' => $data['id'] ?? 'N/A',
                'SKU (seller_custom_field)' => $data['seller_custom_field'] ?? 'No definido',
                'Precio' => '$' . number_format($data['price'] ?? 0, 0, ',', '.'),
                'Stock Disponible' => $data['available_quantity'] ?? 0,
                'Condición' => ($data['condition'] ?? '') === 'new' ? 'Nuevo' : 'Usado',
                'ID de Categoría' => $data['category_id'] ?? 'N/A',
                'Enlace' => $data['permalink'] ?? '#'
            ];

            wp_send_json_success(['producto' => $info_relevante]);
        } else {
            $error_body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = $error_body['message'] ?? 'Error desconocido.';
            wp_send_json_error(['mensaje' => "Error al consultar el producto (HTTP {$http_status}): {$error_message}"]);
        }
    }

    public function tomaridcategorias()
    {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woo_ml_search_nonce')) {
            wp_send_json_error(['mensaje' => '❌ Fallo en la verificación de seguridad. Por favor, recarga la página y vuelve a intentarlo.']);
            return;
        }

        $categoria_objetivo = isset($_POST['categoria_objetivo']) ? sanitize_text_field($_POST['categoria_objetivo']) : '';
    
        if (empty($categoria_objetivo)) {
            wp_send_json_error(['mensaje' => '❌ Debes proporcionar una categoría válida.']);
            return;
        }

        if (!$this->check_and_refresh_token()) {
            wp_send_json_error(['mensaje' => '❌ No se pudo refrescar el token.']);
            return;
        }

        $user_endpoint = WOO_ML_API_ENDPOINT . '/users/me';
        $user_response = $this->make_api_request_sinLog($user_endpoint, 'GET');
        if (is_wp_error($user_response)) {
             wp_send_json_error(['mensaje' => '❌ Error al obtener el ID del vendedor.']);
            return;
        }
        $user_body = json_decode(wp_remote_retrieve_body($user_response), true);
        $seller_id = $user_body['id'];

        $endpoint = WOO_ML_API_ENDPOINT . "/sites/MLC/search?seller_id={$seller_id}&category={$categoria_objetivo}&limit=50";
        $response = $this->make_api_request_sinLog($endpoint, 'GET');
        $http_status = wp_remote_retrieve_response_code($response);

        if ($http_status == 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            $productos_encontrados = [];
            $total = $data['paging']['total'] ?? 0;

            if ($total > 0 && !empty($data['results'])) {
                foreach ($data['results'] as $item) {
                    $productos_encontrados[] = [
                        'Título' => $item['title'] ?? 'N/A',
                        'ID de Publicación' => $item['id'] ?? 'N/A',
                        'Enlace' => $item['permalink'] ?? '#'
                    ];
                }
            }
            
            wp_send_json_success([
                'total' => $total,
                'productos' => $productos_encontrados,
                'mensaje' => "Se encontraron {$total} productos. Mostrando los primeros " . count($productos_encontrados) . "."
            ]);
        } else {
            $error_body = json_decode(wp_remote_retrieve_body($response), true);
            $error_message = $error_body['message'] ?? 'Error desconocido.';
            wp_send_json_error(['mensaje' => "Error al buscar en la categoría (HTTP {$http_status}): {$error_message}"]);
        }
    }


public function importarProductosDesdeArchivo()
{
    // 1. Seguridad y Autenticación
    check_ajax_referer('importar_archivo_nonce', '_wpnonce');
    if (!$this->check_and_refresh_token()) {
        wp_send_json_error(['mensaje' => '❌ No se pudo autenticar con Mercado Libre.']);
        return;
    }

    // 2. Leer y limpiar la lista de IDs pendientes
    $plugin_dir = plugin_dir_path(__FILE__);
    $archivo_ids = $plugin_dir . 'categoria_id.txt';

    if (!file_exists($archivo_ids)) {
        wp_send_json_success(['mensaje' => '✅ No hay archivo de importación.', 'completado' => true, 'restantes' => 0]);
        return;
    }

    $ids_totales_raw = file($archivo_ids, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $ids_totales = array_filter(array_map('trim', $ids_totales_raw));

    if (empty($ids_totales)) {
        @unlink($archivo_ids);
        wp_send_json_success(['mensaje' => '✅ ¡Importación completada!', 'completado' => true, 'restantes' => 0]);
        return;
    }

    // 3. Tomar un lote y preparar variables
    $lote_ids = array_slice($ids_totales, 0, 20);
    $exitosos = [];
    $fallidos = [];
    $ids_procesados_en_lote = [];

    $this->log_debug("📦 Iniciando lote de importación. " . count($lote_ids) . " productos a procesar de " . count($ids_totales) . ".");

    // 4. Obtener detalles del lote en una sola llamada
    $ids_string = implode(',', $lote_ids);
    $details_endpoint = WOO_ML_API_ENDPOINT . "/items?ids={$ids_string}"; // No se necesita attributes=all aquí
    $details_response = $this->make_api_request_v2($details_endpoint, 'GET');

    if (is_wp_error($details_response) || wp_remote_retrieve_response_code($details_response) !== 200) {
        wp_send_json_error(['mensaje' => '❌ Error crítico al obtener detalles del lote desde Mercado Libre.']);
        return;
    }
    
    $ml_products_details = json_decode(wp_remote_retrieve_body($details_response), true);

    // 5. Procesar cada producto de la respuesta de la API
    foreach ($ml_products_details as $producto_lote) {
        $http_code = $producto_lote['code'] ?? 500;
        $item_body = $producto_lote['body'] ?? [];
        $ml_id = $item_body['id'] ?? null;

        if (!$ml_id) {
            // Si no hay ID, no podemos procesarlo
            continue;
        }
        
        $ids_procesados_en_lote[] = $ml_id;

        if ($http_code === 200) {
            try {
                // El producto es válido, procedemos a importar
                $resultado = $this->importarProductoDesdeMercadolibre($ml_id); 
                
                if ($resultado && !is_wp_error($resultado) && !empty($resultado['wc_product_id'])) {
                    $exitosos[] = $ml_id;
                } else {
                    // MEJORA: Capturar el mensaje de error específico si está disponible.
                    $error_detalle = is_wp_error($resultado) ? $resultado->get_error_message() : 'Fallo no especificado (revisar woo-ml-sync.log).';
                    $fallidos[$ml_id] = 'La función de importación interna falló. Detalle: ' . $error_detalle;
                }
            } catch (Exception $e) {
                $fallidos[$ml_id] = $e->getMessage();
            }
        } else {
            // El producto tuvo un error en la API (ej: 404, pausado, etc.)
            $error_msg = $item_body['message'] ?? 'Error desconocido en la API.';
            $fallidos[$ml_id] = "Error {$http_code}: {$error_msg}";
        }
         sleep(1); // Pausa para no saturar el servidor local
    }
    
    // 6. Detectar IDs que fueron solicitados pero no vinieron en la respuesta
    $ids_no_respondidos = array_diff($lote_ids, $ids_procesados_en_lote);
    foreach ($ids_no_respondidos as $id_omitido) {
        $fallidos[$id_omitido] = 'La API de ML no devolvió información para este ID.';
    }

    // 7. Actualizar el archivo de texto, eliminando los IDs procesados (exitosos y fallidos)
    $ids_a_eliminar = array_merge($exitosos, array_keys($fallidos));
    if (!empty($ids_a_eliminar)) {
        $ids_restantes = array_diff($ids_totales, $ids_a_eliminar);
        file_put_contents($archivo_ids, implode(PHP_EOL, $ids_restantes));
    } else {
        $ids_restantes = $ids_totales;
    }

    // 8. Enviar respuesta al frontend
    $total_restante = count($ids_restantes);
    $mensaje_respuesta = "Lote procesado. ✅ Éxitos: " . count($exitosos) . ". ❌ Fallidos: " . count($fallidos) . ".";
    
    wp_send_json_success([
        'mensaje' => $mensaje_respuesta,
        'exitosos' => $exitosos,
        'fallidos' => $fallidos,
        'restantes' => $total_restante,
        'completado' => ($total_restante <= 0)
    ]);
}

public function importar_producto_id() {
    // Revisa que haya ID
    $producto_id = isset($_POST['producto_id']) ? sanitize_text_field($_POST['producto_id']) : '';
    if (!$producto_id) {
        wp_send_json_error([
            'id' => $producto_id,
            'status' => 'error',
            'msg' => 'ID de producto no recibido.'
        ]);
        return;
    }

    // Log interno (no se envía al usuario)
    $this->log_debug("🟡 [AJAX] Importando producto individual ID: $producto_id");

    // Importa usando tu función (debería retornar array con 'success', 'mensaje', etc)
    $resultado = $this->importar_producto_especifico($producto_id);

    // Construye respuesta profesional para el JS
    if (is_wp_error($resultado)) {
        wp_send_json_error([
            'id' => $producto_id,
            'status' => 'error',
            'msg' => $resultado->get_error_message()
        ]);
    } elseif (is_array($resultado) && isset($resultado['success']) && !$resultado['success']) {
        wp_send_json_error([
            'id' => $producto_id,
            'status' => 'error',
            'msg' => $resultado['mensaje'] ?? 'Error desconocido'
        ]);
    } else {
        wp_send_json_success([
            'id' => $producto_id,
            'status' => 'OK',
            'msg' => 'Importado correctamente'
        ]);
    }
}


//01/07

    // public function obtener_ids_productos_por_categoria()
    // {
    //     $SELLER_ID = '1720493754'; // Reemplazar por tu ID real de vendedor
    //     $CATEGORY_ID = 'MLC363814'; // ID de la categoría que quieras consultar

    //     $limit = 50;
    //     $offset = 0;
    //     $todos_los_ids = [];
    //     $access_token = get_option('woo_ml_access_token');
    //     $refresh_token = get_option('woo_ml_refresh_token');
    //     $expiration = get_option('woo_ml_token_expiration');

    //     if (time() >= $expiration) {
    //         $new_token_data = $this->refresh_access_token($refresh_token);
    //         if ($new_token_data && isset($new_token_data['access_token'])) {
    //             $access_token = $new_token_data['access_token'];
    //         } else {
    //             error_log('No se pudo refrescar el token.');
    //             wp_send_json_error(['mensaje' => '❌ No se pudo refrescar el token.']);
    //             return;
    //         }
    //     }

    //     do {
    //         $endpoint = "https://api.mercadolibre.com/sites/MLC/search?seller_id={$SELLER_ID}&category={$CATEGORY_ID}&status=active&limit={$limit}&offset={$offset}";

    //         $response = wp_remote_get($endpoint, [
    //             'headers' => [
    //                 'Authorization' => 'Bearer ' . $access_token
    //             ]
    //         ]);

    //         if (is_wp_error($response)) {
    //             wp_send_json_error(['mensaje' => '❌ Error al consultar la API: ' . $response->get_error_message()]);
    //             return;
    //         }

    //         $body = json_decode(wp_remote_retrieve_body($response), true);
    //         $productos = $body['results'] ?? [];
    //         $total = $body['paging']['total'] ?? 0;

    //         foreach ($productos as $producto) {
    //             $todos_los_ids[] = $producto['id'];
    //         }

    //         $offset += $limit;
    //     } while ($offset < $total);

    //     // Opcional: guardar los IDs en la base de datos de WordPress
    //     update_option('ids_importacion_productos', $todos_los_ids);

    //     wp_send_json_success([
    //         'total_encontrados' => count($todos_los_ids),
    //         'ids' => $todos_los_ids
    //     ]);
    // }

    private function sugerir_sku($producto)
    {
        // Patrones aceptados:
        // - Dos letras - 3 dígitos (Ej: AB-123)
        // - 4 dígitos (Ej: 1234)
        // - Dos letras - 2 dígitos (Ej: AK-99)
        $patron = '/\b(?:[A-Za-z]{1,2}-\d{2,3}|\d{4})\b/';

        if (preg_match($patron, $producto->get_name(), $coincidencia)) {
            return $coincidencia[0];
        } else {
            // Si no está en el título, se busca en la variación
            $this->log_debug("Producto " . $producto->get_name() . " no tiene SKU en el título");
            foreach ($producto->get_available_variations() as $variacion) {
                $sku = $variacion->get_sku();
                if ($sku && preg_match($patron, $sku, $coincidencia)) {
                    $this->log_debug("Código de producto encontrado en SKU de variacion: " . $sku);
                    return $coincidencia[0];
                }
            }
        }

        return null; // Retorna null si no encuentra un SKU válido
    }

   private function sugerir_seller_custom_field($producto_ml)
    {
    // 1. Intentar extraer un código desde el título
    $patron = '/\b(?:[A-Za-z]{1,2}-\d{2,3}|\d{4})\b/';
    if (preg_match($patron, $producto_ml['title'], $coincidencia)) {
        $this->log_debug("Código encontrado en el título usando patrón: " . $coincidencia[0]);
        return $coincidencia[0];
    }

    // 2. Buscar SKU en los atributos del producto principal
    if (!empty($producto_ml['attributes'])) {
        foreach ($producto_ml['attributes'] as $atributo) {
            if ($atributo['id'] === 'SELLER_SKU' && !empty($atributo['value_name'])) {
                $this->log_debug("SKU encontrado en atributos del producto: " . $atributo['value_name']);
                return $atributo['value_name'];
            }
        }
    }

    // 3. Buscar SKU en las variaciones
    if (!empty($producto_ml['variations'])) {
        foreach ($producto_ml['variations'] as $variacion) {
            $sku = array_column($variacion['attributes'], 'value_name', 'id')['SELLER_SKU'] ?? null;
            if ($sku) {
                $this->log_debug("SKU encontrado en variación: " . $sku);
                return $sku;
            }
        }
    }

    $this->log_debug("SKU no encontrado en título, atributos ni variaciones");
    return null;
    }
    public function asignar_sku()
    {
        $status = $_POST['status'] ?? "recopilando";
        $total = $_POST['total'] ?? 0;
        $exitosos = $_POST['exitosos'] ?? 0;
        $fallidos = ($_POST['fallidos'] == "[]") ? [] : json_decode(stripslashes($_POST['fallidos']), true);
        $pendientes = ($_POST['pendientes'] == "[]") ? [] : json_decode(stripslashes($_POST['pendientes']), true);

        if ($status == 'recopilando') {
            $this->log_debug("*** Asignando SKU a los productos de WooCommerce ***");

            $args = array(
                'limit' => -1, // Obtener todos los productos
                'status' => 'publish', // Solo productos publicados
                'category' => array('pijamas'), //Categoria pijamas
                'return' => 'objects', // Retorna una lista de objetos WC_Product
            );

            $productos_wc = wc_get_products($args);
            $total = count($productos_wc);
            $procesados = 0;
            foreach ($productos_wc as $producto) {
                $pendientes[] = $producto->get_id();
                $procesados++;
                $this->log_debug("Recopilando IDs de productos: " . $procesados . " de " . $total);
            }
        }

        if ($status == 'procesando') {
            $procesados = $exitosos + count($fallidos);
            $this->log_debug("Se han procesado " . $procesados . " de " . $total . " productos");
            try {
                $id_producto = array_shift($pendientes);
                $producto = wc_get_product($id_producto);
                $sku_sugerido = $this->sugerir_sku($producto);
                $producto->set_sku($sku_sugerido);
                $producto->save();
                $this->log_debug("SKU del producto " . $producto->get_name() . " modificado a: " . $producto->get_sku());
                $exitosos++;
            } catch (Exception $e) {
                $fallidos[] = $producto->get_id();
                $this->log_error("Problemas al modificar SKU del producto " . $producto->get_name() . ": " . $e->getMessage());
            }
        }

        if ($status == 'recopilando') {
            $status = "procesando";
            $this->log_debug("Se han recopilado todos los IDs");
        }

        if ($exitosos + count($fallidos) == $total) {
            $status = "finalizado";
            $this->log_debug("Se ha asignado SKU a todos los productos de la categoría PIJAMAS");
            $this->log_debug("Productos fallidos: " . print_r($fallidos, true));
        }

        wp_send_json_success([
            'status' => $status,
            'exitosos' => $exitosos,
            'fallidos' => $fallidos,
            'total' => $total,
            'pendientes' => $pendientes,
        ]);
    }

    private function crear_sku($ml_id)
    {
        $producto_ml = $this->obtener_producto_ml($ml_id);
        if (!$producto_ml) {
            $this->log_error("❌ No se pudo obtener el producto con ID: $ml_id");
            return false;
        }

        $codigo = $this->sugerir_seller_custom_field($producto_ml);

        if (empty($codigo)) {
            $this->log_error("❌ No se pudo sugerir un código para el producto: $ml_id");
            return false;
        }

        $data['seller_custom_field'] = $codigo;
        $endpoint = WOO_ML_API_ENDPOINT . '/items/' . $ml_id;

        $response = $this->make_api_request_v2($endpoint, 'PUT', $data);

        // Verificar respuesta
        //if (isset($response['id']) && $response['id'] === $ml_id) {
        //    $this->log_debug("✅ SKU actualizado correctamente en producto $ml_id");
        //    return true;
        //} else {
        //    $this->log_error("❌ Error al actualizar SKU en producto $ml_id");
        //    $this->log_error("Respuesta de la API: " . json_encode($response));
        //   return false;
        //}
        $body = json_decode(wp_remote_retrieve_body($response), true);

    if (isset($body['id']) && $body['id'] === $ml_id) {
        $this->log_debug("✅ SKU actualizado correctamente en producto $ml_id");
        return true;
    } else {
        $this->log_error("❌ Error al actualizar SKU en producto $ml_id");
        $this->log_error("Respuesta de la API: " . json_encode($body));
        return false;
    }
    }

    public function asignar_seller_custom_field()
    {
        $limit = 5;
    $status = "importando";
    $offset = $_POST['offset'] ?? 0;
    $productos_importados = $_POST['importados'] ?? 0;
    $SELLER_ID = $this->get_user_id();
    $CATEGORY_ID = "MLC363814"; // Categoría corregida a "Accesorios para Vehículos > Limpieza de Vehículos > Tratamientos"

    // Primero obtenemos el nombre de la categoría
    $category_endpoint = "https://api.mercadolibre.com/categories/" . $CATEGORY_ID;
    $category_response = $this->make_api_request_v2($category_endpoint, 'GET');
    $category_data = json_decode(wp_remote_retrieve_body($category_response), true);
    $category_name = $category_data['name'] ?? 'Nombre de categoría no disponible';

    $endpoint = "https://api.mercadolibre.com/sites/MLC/search?seller_id=" . $SELLER_ID . "&category=" . $CATEGORY_ID . "&status=active&limit=" . $limit . "&offset=" . $offset;
    $response = $this->make_api_request_v2($endpoint, 'GET');
    $body = json_decode(wp_remote_retrieve_body($response), true);
    $productos = $body['results'];
    $total_productos = $body['paging']['total'];

    $this->log_debug("**************************************************");
    $this->log_debug("INICIANDO ASIGNACIÓN DE SELLER_CUSTOM_FIELD");
    $this->log_debug("Categoría: " . $category_name . " - (" . $CATEGORY_ID . ")");
    $this->log_debug("Total productos en categoría: " . $total_productos);
    $this->log_debug("Procesando lote: " . ($offset + 1) . " a " . min($offset + $limit, $total_productos));
    $this->log_debug("Total procesados hasta ahora: " . $productos_importados);
    $this->log_debug("**************************************************");

    foreach ($productos as $producto) {
        $this->log_debug("Procesando producto ID: " . $producto['id'] . " - " . $producto['title']);
        $resultado = $this->crear_sku($producto['id']);

        if ($resultado) {
            $this->log_debug("SKU asignado correctamente al producto " . $producto['id']);
        } else {
            $this->log_error("Error al asignar SKU al producto " . $producto['id']);
        }

        $productos_importados++;
        $this->log_debug("Progreso: " . $productos_importados . "/" . $total_productos . " completados");
        $this->log_debug("----------------------------------------");
    }

    if ($productos_importados >= $total_productos) {
        $status = "finalizado";
        $this->log_debug("**************************************************");
        $this->log_debug("PROCESO COMPLETADO");
        $this->log_debug("Total productos procesados: " . $productos_importados);
        $this->log_debug("Se ha agregado SELLER_CUSTOM_FIELD a todos los productos");
        $this->log_debug("**************************************************");
    }

    // Respuesta final
    wp_send_json_success([
        'status' => $status,
        'offset' => $offset + $limit,
        'importados' => $productos_importados,
        'total' => $total_productos,
        'category_name' => $category_name // Opcional: enviar el nombre en la respuesta
    ]);
    }

    public function verificar_seller_custom_field()
    {
               $status = $_POST['status'] ?? "recopilando";
        $pendientes = ($_POST['pendientes'] == "[]") ? [] : json_decode(stripslashes($_POST['pendientes']), true);
        $datos = ($_POST['datos'] == "[]") ? [] : json_decode(stripslashes($_POST['datos']), true);
        $sin_sku = ($_POST['sin_sku'] == "[]") ? [] : json_decode(stripslashes($_POST['sin_sku']), true);
        $total = $_POST['total'] ?? 0;
        $importados = $_POST['importados'] ?? 0;

        if ($status == "recopilando") {
            $limit = 50;
            $offset = $_POST['offset'] ?? 0;
            $SELLER_ID = $this->get_user_id();
            $CATEGORY_ID = "MLC363814";
            $endpoint = "https://api.mercadolibre.com/sites/MLC/search?seller_id=" . $SELLER_ID . "&category=" . $CATEGORY_ID . "&limit=" . $limit . "&offset=" . $offset;
            $response = $this->make_api_request_v2($endpoint, 'GET');
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $total = $body['paging']['total'];
            $productos = $body['results'];
            foreach ($productos as $producto) {
                $pendientes[] = $producto['id'];
            }
        }

        if ($status == "importando") {
            $id_producto = array_shift($pendientes);
            $producto_ml = $this->obtener_producto_ml($id_producto);

            $datos[] = [
                'id' => $producto_ml['id'],
                'title' => $producto_ml['title'],
                'seller_custom_field actual' => $producto_ml['seller_custom_field'],
                'seller_custom_field sugerido' => $this->sugerir_seller_custom_field($producto_ml),
            ];

            if (empty($producto_ml['seller_custom_field'])) {
                $sin_sku[] = $producto_ml['id'];
            }

            $importados++;
            $this->log_debug("Productos procesados: " . $importados . "  de " . $total);
        }

        if ($importados == $total) {
            $status = "finalizado";
            $this->log_debug("Se han procesado todos los productos en la categoría.");
            $this->log_debug("Productos sin SKU: " . print_r($sin_sku, true));
            $this->log_debug("Información de WooCommerce: " . print_r($datos, true));
        }

        if ($offset >= $total) {
            $status = "importando";
            $this->log_debug("Se han terminado de recopliar los IDs.");
        }

        // Respuesta final
        wp_send_json_success([
            'status' => $status,
            'offset' => $offset + $limit,
            'importados' => $importados,
            'total' => $total,
            'sin_sku' => $sin_sku,
            'pendientes' => $pendientes,
            'datos' => $datos,
        ]);
    }

    public function verificar_sku()
    {
        $this->log_debug("*** Procesando SKU de los productos de WooCommerce ***");

        $args = array(
            'limit' => -1, // Obtener todos los productos
            'status' => 'publish', // Solo productos publicados
            'category' => array('pijamas'), //Categoria pijamas
            'return' => 'objects', // Retorna una lista de objetos WC_Product
        );

        $productos_wc = wc_get_products($args);
        $con_sku = [];
        $sin_sku = [];

        foreach ($productos_wc as $producto) {
            $sku_sugerido = $this->sugerir_sku($producto);
            $con_sku[] = [
                'titulo' => $producto->get_name(),
                'sku' => $producto->get_sku(),
                'sku sugerido' => $sku_sugerido,
            ];
            if (empty($producto->get_sku())) {
                $sin_sku[] = $producto->get_name();
            }
        }
        $this->log_debug("Total de productos: " . count($productos_wc));
        $this->log_debug("Productos sin SKU: " . print_r($sin_sku, true));
        $this->log_debug("Información de WooCommerce: " . print_r($con_sku, true));
    }

    //Verificar la edicion de productos y variaciones
     public function importar_pijamas()
    {
        $status = $_POST['status'] ?? "capturando_ids";
        $pendientes = (empty($_POST['pendientes']) || $_POST['pendientes'] == "[]") ? [] : json_decode(stripslashes($_POST['pendientes']), true);
        $importados = $_POST['importados'] ?? 0;
        $total = $_POST['total'] ?? 0;
        $offset = $_POST['offset'] ?? 0;
        $limit = 50;

        // Definimos fuera para que esté disponible en ambos bloques
        $CATEGORY_ID = "MLC363814";
        $SELLER_ID = $this->get_user_id();
        $category_name = $CATEGORY_ID; // fallback

        // Obtener nombre de categoría solo una vez (para log)
        if ($status == "capturando_ids" && $offset == 0) {
            $category_endpoint = "https://api.mercadolibre.com/categories/{$CATEGORY_ID}";
            $category_response = $this->make_api_request_v2($category_endpoint, 'GET');
            $category_data = json_decode(wp_remote_retrieve_body($category_response), true);
            $category_name = $category_data['name'] ?? $CATEGORY_ID;
        }

        if ($status == "capturando_ids") {
            $endpoint = "https://api.mercadolibre.com/sites/MLC/search?seller_id={$SELLER_ID}&category={$CATEGORY_ID}&status=active&limit={$limit}&offset={$offset}";
            $response = $this->make_api_request_v2($endpoint, 'GET');
            $body = json_decode(wp_remote_retrieve_body($response), true);

            $total = $body['paging']['total'] ?? 0;
            $productos = $body['results'] ?? [];

            $this->log_debug("**************************************************");
            $this->log_debug("INICIANDO IMPORTACIÓN DE PRODUCTOS");
            $this->log_debug("Categoría: " . $category_name);
            $this->log_debug("Total productos en categoría: " . $total);
            $this->log_debug("Procesando lote: " . ($offset + 1) . " a " . min($offset + $limit, $total));
            $this->log_debug("**************************************************");

            foreach ($productos as $producto) {
                $pendientes[] = $producto['id'];
                $this->log_debug("ID capturado para importación: " . $producto['id'] . " - " . $producto['title']);
            }

            // Si ya llegamos al final de los productos, cambiamos el estado
            if (($offset + $limit) >= $total) {
                $status = "importando";
                $this->log_debug("Se han capturado todos los IDs de productos");
                $this->log_debug("Iniciando proceso de importación...");
            }
        }

        if ($status == "importando") {
            if (!empty($pendientes)) {
                $id_producto = array_shift($pendientes);
                $this->log_debug("Importando producto ID: " . $id_producto);

                $resultado = $this->importarProductoDesdeMercadolibre($id_producto);
                $importados++;

                if ($resultado) {
                    $this->log_debug("Producto importado correctamente: " . $id_producto);
                } else {
                    $this->log_error("Error al importar producto: " . $id_producto);
                }

                $this->log_debug("Progreso: {$importados}/{$total} completados");
                $this->log_debug("----------------------------------------");

                if ($importados >= $total) {
                    $status = "finalizado";
                    $this->log_debug("**************************************************");
                    $this->log_debug("IMPORTACIÓN COMPLETADA");
                    $this->log_debug("Total productos importados: " . $importados);
                    $this->log_debug("Categoría: " . $category_name);
                    $this->log_debug("**************************************************");
                }
            } else {
                $this->log_debug("No hay más productos pendientes.");
                $status = "finalizado";
            }
        }

        wp_send_json_success([
            'status' => $status,
            'offset' => ($status == "capturando_ids") ? $offset + $limit : $offset,
            'importados' => $importados,
            'pendientes' => $pendientes,
            'total' => $total,
        ]);
    }

    private function get_user_id()
    {
        $endpoint_user = WOO_ML_API_ENDPOINT . '/users/me';
        $response_user = $this->make_api_request_sinLog($endpoint_user, 'GET');
        if (is_wp_error($response_user)) {
            $this->log_error("Problema al obtener user ID");
            return false;
        }
        $user = json_decode(wp_remote_retrieve_body($response_user), true);
        $user_id = $user['id'];
        return $user_id;
    }

    private function get_product_domain($category_id)
    {
        $endpoint_dominio = "https://api.mercadolibre.com/categories/" . $category_id;
        $response_dominio = $this->make_api_request_sinLog($endpoint_dominio, 'GET');
        if (is_wp_error($response_dominio)) {
            $this->log_error("Problema al obtener dominio del producto");
            return false;
        }
        $body_dominio = json_decode(wp_remote_retrieve_body($response_dominio), true);
        $dominio = $body_dominio['settings']['catalog_domain'];
        return $dominio;
    }

    private function check_domain($dominio)
    {
        $endpoint_disponible = "https://api.mercadolibre.com/catalog/charts/MLC/configurations/active_domains";
        $response_disponible = $this->make_api_request_sinLog($endpoint_disponible, 'GET');
        if (is_wp_error($response_disponible)) {
            $this->log_error("Problema al verificar si el dominio está activo");
            return false;
        }
        $body_disponible = json_decode(wp_remote_retrieve_body($response_disponible), true);
        $array_dominios = $body_disponible['domains'];
        $dominios_activos = array_column($array_dominios, 'domain_id');
        if (!in_array($dominio, $dominios_activos)) {
            $this->log_debug("El producto no necesita guia de tallas");
            return false;
        }
        return true;
    }

    private function get_domain_tech_specs($dominio)
    {
        $endpoint_domain = WOO_ML_API_ENDPOINT . "/domains/" . $dominio . "/technical_specs";
        $response_domain = $this->make_api_request_sinLog($endpoint_domain, 'GET');
        $body_domain = json_decode(wp_remote_retrieve_body($response_domain), true);
        return $body_domain;
    }

    private function get_grid_filter($tech_specs)
    {
        //Obtener filtros obligatorios del dominio para encontrar guia de tallas
        $listaAtributos = array();
        foreach ($tech_specs['input']['groups'] as $grupo) {
            foreach ($grupo['components'] as $componente) {
                foreach ($componente['attributes'] as $atributo) {
                    if (isset($atributo['tags']) && in_array('required', $atributo['tags']) && in_array('grid_filter', $atributo['tags'])) {
                        $listaAtributos[] = $atributo['id'];
                        $this->log_debug("Filtrando por: " . $atributo['id']);
                    }
                }
            }
        }
        return $listaAtributos;
    }

    private function get_grid_template_required($tech_specs)
    {
        $grid_template_required = [];
        foreach ($tech_specs['input']['groups'] as $grupo) {
            foreach ($grupo['components'] as $componente) {
                foreach ($componente['attributes'] as $atributo) {
                    if (isset($atributo['tags']) && in_array('grid_template_required', $atributo['tags'])) {
                        $grid_template_required[] = $atributo['id'];
                        $this->log_debug("Encontrando ficha técnica por: " . $atributo['id']);
                    }
                }
            }
        }
        return $grid_template_required;
    }

    private function prepare_find_size_grid_data($metadatos, $dominio, $user_id, $grid_filter)
    {
        //Data para encontrar guia de tallas
        $data = array(
            'domain_id' => str_replace("MLC-", "", $dominio),
            'site_id' => 'MLC',
            'seller_id' => $user_id,
            'attributes' => array(),
        );

        //Obtenemos el valor de los atributos a filtrar de los metadatos
        foreach ($metadatos['attributes'] as $atributo) {
            if (in_array($atributo['id'], $grid_filter)) {
                $data['attributes'][] = array(
                    'id' => $atributo['id'],
                    'values' => array(
                        array('name' => $atributo['value_name']),
                    ),
                );
            }
        }

        return $data;
    }

    private function prepare_data_ft_sizegrid($metadatos, $grid_template_required)
    {
        $data_ft_sizegrid = [];
        foreach ($metadatos['attributes'] as $atributo_metadato) {
            if (in_array($atributo_metadato['id'], $grid_template_required)) {
                $data_ft_sizegrid['attributes'][] = $atributo_metadato;
            }
        }
        return $data_ft_sizegrid;
    }

    // private function eliminar_size_grid()
    // {
    //     $this->log_debug("Eliminando guias de tallas no usadas");
    //     $producto_wc = wc_get_product(2625);
    //     if (empty($producto_wc)) {
    //         $this->log_debug("Error al obtener producto de WooCommerce");
    //         return;
    //     }
    //     $metadatos = get_post_meta($producto_wc->get_id(), '_datos_mercadolibre', true);
    //     if (empty($metadatos)) {
    //         $this->log_debug("Error al obtener metadatos");
    //         return;
    //     }
    //     $dominio = "MLC-PAJAMAS";
    //     $user_id = $this->get_user_id();
    //     $tech_specs = $this->get_domain_tech_specs($dominio);
    //     $grid_filter = $this->get_grid_filter($tech_specs);
    //     $data_chart = $this->prepare_find_size_grid_data($metadatos, $dominio, $user_id, $grid_filter);

    //     $endpoint_chart = "https://api.mercadolibre.com/catalog/charts/search";
    //     $response_chart = $this->make_api_request_sinLog($endpoint_chart, 'POST', $data_chart);
    //     $body_chart = json_decode(wp_remote_retrieve_body($response_chart), true);

    //     if (empty($body_chart['charts'])) {
    //         $this->log_debug("No se ha encontrado ninguna guía de talla para el producto.");
    //         return null;
    //     }

    //     $this->log_debug("Guias de tallas encontradas: " . $body_chart['paging']['total']);

    //     foreach ($body_chart['charts'] as $guia_de_tallas) {
    //         $this->log_debug("Eliminando guia de tallas con ID: " . $guia_de_tallas['id']);
    //         $endpoint_delete = "https://api.mercadolibre.com/catalog/charts/" . $guia_de_tallas['id'];
    //         $response_delete = $this->make_api_request($endpoint_delete, 'DELETE');
    //     }
    // }

    private function encontrar_size_grid($metadatos, $dominio, $user_id, $tallas_wc)
    {
        $this->log_debug("***");
        $this->log_debug("Buscando guia de tallas.");
        //$this->log_debug("Tallas del producto en WooCommerce: " . print_r($tallas_wc, true));
        $tech_specs = $this->get_domain_tech_specs($dominio);
        $grid_filter = $this->get_grid_filter($tech_specs);
        $data_chart = $this->prepare_find_size_grid_data($metadatos, $dominio, $user_id, $grid_filter);

        $endpoint_chart = "https://api.mercadolibre.com/catalog/charts/search";
        $response_chart = $this->make_api_request_sinLog($endpoint_chart, 'POST', $data_chart);
        $body_chart = json_decode(wp_remote_retrieve_body($response_chart), true);

        if (empty($body_chart['charts'])) {
            $this->log_debug("No se ha encontrado ninguna guía de talla para el producto.");
            return null;
        }

        $this->log_debug("Guias de tallas encontradas: " . $body_chart['paging']['total']);

        foreach ($body_chart['charts'] as $guia_de_tallas) {
            $tallas_size_grid = [];
            foreach ($guia_de_tallas['rows'] as $row) {
                foreach ($row['attributes'] as $atributo) {
                    if ($atributo['id'] == "SIZE") {
                        foreach ($atributo['values'] as $valor) {
                            //Evitar valores duplicados
                            if (!in_array($valor['name'], $tallas_size_grid)) {
                                //Almacenar los valores de la guia de tallas
                                $tallas_size_grid[] = $valor['name'];
                                //$this->log_debug("Talla encontrada en size grid: " . $valor['name']);
                            }
                        }
                    }
                }
            }

            //Comparar tallas de WooCommerce con tallas de la guia de tallas
            if (empty(array_diff($tallas_wc, $tallas_size_grid))) {
                $this->log_debug("GUIA DE TALLAS IDEAL ENCONTRADA: " . $guia_de_tallas['id']);
                return $guia_de_tallas['id'];
            }
        }
        $this->log_debug("No se ha encontrado una guia de tallas acorde al producto");
        return null;
    }

    private function encontrar_ficha_tecnica($metadatos, $dominio)
    {
        $tech_specs = $this->get_domain_tech_specs($dominio);
        $grid_template_required  = $this->get_grid_template_required($tech_specs);
        $data_ft_sizegrid = $this->prepare_data_ft_sizegrid($metadatos, $grid_template_required);
        $endpoint_ft_sizegrid = "https://api.mercadolibre.com/domains/" . $dominio . "/technical_specs?section=grids";
        $response_ft_sizegrid =  $this->make_api_request_v2($endpoint_ft_sizegrid, 'POST', $data_ft_sizegrid);
        $body_ft_sizegrid = json_decode(wp_remote_retrieve_body($response_ft_sizegrid), true);
        return $body_ft_sizegrid;
    }

    private function encontrar_atributos_size_grid($ficha_tecnica)
    {
        //Atributos requeridos
        $attributes = [];
        $rows = [];
        foreach ($ficha_tecnica['input']['groups'] as $group) {
            foreach ($group['components'] as $component) {
                if (isset($component['components'])) {
                    foreach ($component['components'] as $subcomponent) {
                        if (isset($subcomponent['attributes'])) {
                            foreach ($subcomponent['attributes'] as $attribute) {

                                //Atribtuttes
                                if (in_array('required', $attribute['tags']) && in_array('grid_filter', $attribute['tags'])) {
                                    $attributes[] = $attribute['id'];
                                    $this->log_debug("Atributo: " . $attribute['id']);
                                }

                                //Rows
                                $tags_rows = ['allow_variations', 'variation_attribute', 'BODY_MEASURE'];
                                if (in_array('required', $attribute['tags']) && array_intersect($tags_rows, $attribute['tags'])) {
                                    $rows[] = $attribute;
                                    $this->log_debug("Rows: " . $attribute['id']);
                                }

                                //Main Attribute
                                if (in_array('main_attribute_candidate', $attribute['tags'])) {
                                    $main_attribute = $attribute['id'];
                                    $this->log_debug("Atributo Principal: " . $attribute['id']);
                                    //Calzado
                                    if ($attribute['id'] == "CL_SIZE") {
                                        $rows[] = $attribute['id'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $atributos_obligatorios = [
            'attributes' => $attributes,
            'rows' => $rows,
            'main_attribute' => $main_attribute,
        ];

        return $atributos_obligatorios;
    }

    private function asignar_valor_atributos_size_grid($metadatos, $attributes)
    {
        $attributes_metadatos = [];
        foreach ($metadatos['attributes'] as $atributo_metadato) {
            //Ahora es un array compuesto
            if (in_array($atributo_metadato['id'], $attributes)) {
                $attributes_metadatos[] = $atributo_metadato;
            }
        }
        return $attributes_metadatos;
    }

    private function buscar_atributos_metadatos($variacion, $rows)
    {
        $atributos_existentes_fila = [];
        $atributos_existentes_size_grid = [];
        foreach ($variacion['attribute_combinations'] as $atributo) {
            if (in_array($atributo['id'], $rows)) {
                //Evitar atributos duplicados
                if (!in_array($atributo['id'], $atributos_existentes_fila)) {
                    $new_row['attributes'][] = $atributo;
                    $atributos_existentes_fila[] = $atributo['id'];
                    //Agregar el atributo con su valor
                    if (!in_array($atributo['id'], $atributos_existentes_size_grid)) {
                        $atributos_existentes_size_grid[] = $atributo['id'];
                    }
                }
            }
        }

        $atributos_obligatorios = [
            'new_row' => $new_row,
            'fila' => $atributos_existentes_fila,
            'size_grid' => $atributos_existentes_size_grid,
        ];

        return $atributos_obligatorios;
    }

    private function crear_atributo_unit_number($row, $number)
    {
        $unit = $row['default_unit_id'];
        $atributo = [
            'id' => $row['id'],
            'name' => $row['name'],
            'values' => array(
                array(
                    'name' => $number . " " . $unit,
                    'struct' => array(
                        'number' => $number,
                        'unit' => $unit,
                    ),
                ),
            ),
        ];
        $this->log_debug("Creado atributo unit_number: " . $atributo['id']);
        return $atributo;
    }

    private function crear_atributo_list($row, $size)
    {
        $size = $this->formatear_talla($size);
        foreach ($row['values'] as $valor) {
            if ($valor['name'] == $size) {
                $atributo = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'values' => array(
                        array(
                            'id' => $valor['id'],
                            'name' => $size,
                        ),
                    ),
                ];
                $this->log_debug("Creado atributo list: " . $atributo['id']);
                return $atributo;
            }
        }
        $this->log_debug("** La talla: " . $size . " no se encuentra en filtrable_size. Formateando. **");
        //Buscar el número en las edades posibles de filtrable_size
        foreach ($row['values'] as $producto) {
            $partes = explode(" ", $producto['name']);
            if ($partes[0] == $size) {
                $atributo = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'values' => array(
                        array(
                            'id' => $producto['id'],
                            'name' => $producto['name'],
                        ),
                    ),
                ];
                $this->log_debug("Creado atributo list: " . $atributo['id']);
                return $atributo;
            }
        }
        //$this->log_debug("Posibles valores de filtrable_size: " . print_r($row['values'], true));
    }

    private function formatear_talla($talla)
    {
        $talla_formateada = $this->quitar_x($talla);
        $talla_formateada = $this->quitar_slash($talla_formateada);
        return $talla_formateada;
    }
    private function quitar_slash($talla)
    {
        return explode("/", $talla)[0];
    }

    private function quitar_x($talla)
    {
        // Si la talla tiene exactamente una 'X' al inicio, devolverla sin cambios
        if (preg_match('/^X[^X]/', $talla)) {
            return $talla;
        }

        // Contar la cantidad de 'X' consecutivas al inicio
        preg_match('/^X+/', $talla, $coincidencias);

        if (!empty($coincidencias[0])) {
            $numX = strlen($coincidencias[0]); // Contar cantidad de 'X'
            $resto = substr($talla, $numX); // Obtener el resto de la talla
            return $numX . 'X' . $resto; // Formatear salida
        }

        return $talla; // Retornar la misma talla si no tiene 'X'
    }

    private function asignar_valor_filas_size_grid($metadatos, $rows)
    {
        $tallas_existentes = []; // Evita repetir tallas y filas
        $number = 35; // Evita repetir valor al crear un atributo tipo number_unit
        $rows_metadatos = []; //Array final
        foreach ($metadatos['variations'] as $variacion) {

            //Verificar si la talla de la variacion ya existe en la guia de tallas
            $size = array_column($variacion['attribute_combinations'], 'value_name', 'id')['SIZE'] ?? null;
            if (in_array($size, $tallas_existentes)) {
                $this->log_debug("La talla de la variacion ya se encuentra en la guia de tallas");
                continue;
            }
            $tallas_existentes[] = $size;

            $new_row = []; //Nueva fila
            $atributos_existentes_fila = []; //No se deben repetir a nivel de fila
            $rows_ids = array_column($rows, "id");

            //$attributes = $this->buscar_atributos_metadatos($variacion, $rows_ids, $atributos_existentes_fila, $atributos_existentes_size_grid, $new_row, 'attributes');
            //$this->log_debug("Atributos existentes en la guia de tallas en atributos: " . print_r($attributes, true));    
            $attribute_combinations = $this->buscar_atributos_metadatos($variacion, $rows_ids);
            $atributos_existentes_fila = $attribute_combinations['fila'];
            $new_row = $attribute_combinations['new_row'];

            //Crear atributos que no estaban en los metadatos
            foreach ($rows as $row) {
                if (!in_array($row['id'], $atributos_existentes_fila)) {
                    if ($row['value_type'] == "number_unit") {
                        $atributo = $this->crear_atributo_unit_number($row, $number);
                        $number++;
                    } else if ($row['value_type'] == "list") {
                        $atributo = $this->crear_atributo_list($row, $size);
                    }
                    $new_row['attributes'][] = $atributo;
                }
            }
            //$this->log_debug("Añadiendo fila: " . print_r($new_row, true));
            $rows_metadatos[] = $new_row;
        }
        return $rows_metadatos;
    }

    public function crear_size_grid($metadatos, $dominio)
    {
        $this->log_debug("***");
        $this->log_debug("Creando guia de tallas para el producto");
        $ficha_tecnica = $this->encontrar_ficha_tecnica($metadatos, $dominio);

        //$this->log_debug("Revisar estructura de los atributos para su correcta creación: " . print_r($ficha_tecnica, true));

        $atributos_size_grid = $this->encontrar_atributos_size_grid($ficha_tecnica);
        $attributes_metadatos = $this->asignar_valor_atributos_size_grid($metadatos, $atributos_size_grid['attributes']);
        $rows_metadatos = $this->asignar_valor_filas_size_grid($metadatos, $atributos_size_grid['rows']);

        $nombre = substr(bin2hex(random_bytes(16)), 0, 16); //Dominio + Attributes_metadatos
        $data = [
            "names" => [
                "MLC" => $nombre,
            ],
            "domain_id" => str_replace("MLC-", "", $dominio),
            "site_id" => "MLC",
            "main_attribute" => [
                "attributes" => [
                    [
                        "site_id" => "MLC",
                        "id" => $atributos_size_grid['main_attribute'],
                    ]
                ],
            ],
            "attributes" => $attributes_metadatos,
            "rows" => $rows_metadatos,

        ];

        //$this->log_debug("SIZE GRID: " . print_r($data, true));

        $endpoint_crear = "https://api.mercadolibre.com/catalog/charts";
        $response_crear = $this->make_api_request_v2($endpoint_crear, 'POST', $data);
        $guia_de_tallas_creada = json_decode(wp_remote_retrieve_body($response_crear), true);
        $this->log_debug("Guia de tallas creada correctamente");
        $this->log_debug("***");
        return $guia_de_tallas_creada['id'];
    }

    //Revisar 
    public function crearSku()
    {
        try {
            // Verifica nonce para evitar ataques CSRF
            check_ajax_referer('crearSku_nonce', 'nonce');

            // Verifica y refresca el token
            if (!$this->check_and_refresh_token()) {
                wp_send_json_error(__('Error: Token inválido o caducado.', 'woo-ml-sync'));
            }

            // Recupera el scroll_id, retry_ids y failed_ids desde el frontend
            $scroll_id = isset($_POST['scroll_id']) ? sanitize_text_field($_POST['scroll_id']) : null;
            $retry_ids = isset($_POST['retry_ids']) ? json_decode(stripslashes($_POST['retry_ids']), true) : [];
            $failed_ids = isset($_POST['failed_ids']) ? json_decode(stripslashes($_POST['failed_ids']), true) : [];
            $state = isset($_POST['state']) ? sanitize_text_field($_POST['state']) : 'creating'; // Estado "creating" o "retrying"
            $limit = 50;
            $max_retries = 10;

            // Obtener el usuario de MercadoLibre
            $user_endpoint = WOO_ML_API_ENDPOINT . '/users/me';
            $user_response = $this->make_api_request_v2($user_endpoint, 'GET');
            if (is_wp_error($user_response)) {
                $this->log_error('Error al obtener el usuario de MercadoLibre: ' . $user_response->get_error_message());
                wp_send_json_error(__('Error al obtener el usuario de MercadoLibre.', 'woo-ml-sync'));
            }
            $user_body = json_decode(wp_remote_retrieve_body($user_response), true);
            $user_id = $user_body['id'];

            // Preparar endpoint de búsqueda con scroll_id
            $products_endpoint = $scroll_id
                ? WOO_ML_API_ENDPOINT . "/users/{$user_id}/items/search?limit={$limit}&search_type=scan&scroll_id={$scroll_id}"
                : WOO_ML_API_ENDPOINT . "/users/{$user_id}/items/search?limit={$limit}&search_type=scan";

            $products_response = $this->make_api_request_v2($products_endpoint, 'GET');
            if (is_wp_error($products_response)) {
                $this->log_error('Error al obtener productos de MercadoLibre: ' . $products_response->get_error_message());
                wp_send_json_error(__('Error al obtener productos de MercadoLibre.', 'woo-ml-sync'));
            }

            $products_body = json_decode(wp_remote_retrieve_body($products_response), true);
            $productosML = $products_body['results'] ?? [];
            $new_scroll_id = $products_body['scroll_id'] ?? null;

            // Inicializar contadores
            $synced = $failed = 0;

            foreach ($productosML as $ml_product_id) {
                // Usar la función para crear o actualizar el SKU
                $result = $this->create_or_update_ml_product_sku($ml_product_id);
                if (is_wp_error($result)) {
                    $error_code = $result->get_error_code();

                    // Manejar errores específicos
                    if ($error_code === 'too_many_requests') {
                        //$retry_ids[$ml_product_id] = $current_retries + 1; // Increase retry count
                    } elseif ($error_code === 'under_review') {
                        $failed_ids[$ml_product_id] = true;
                        $this->log_error("Producto ID: {$ml_product_id} - Error: En revisión.");
                    } else {
                        $failed_ids[$ml_product_id] = true;
                        $this->log_error("Producto ID: {$ml_product_id} - Error desconocido: " . $result->get_error_message());
                    }
                    continue;
                }

                // Si el producto se actualizó correctamente
                $synced++;
            }

            foreach ($retry_ids as $ml_product_id => $current_retries) {
                if ($current_retries >= $max_retries) {
                    $failed_ids[$ml_product_id] = true;
                    unset($retry_ids[$ml_product_id]);
                    $failed++;
                    continue;
                }
            }

            if (empty($retry_ids) && empty($productosML)) {
                $state = 'completed';
            }

            wp_send_json_success([
                'message' => __('Productos procesados correctamente.', 'woo-ml-sync'),
                'scroll_id' => $new_scroll_id,
                'has_more' => !(empty($productosML) && empty($retry_ids)),
                'retry_ids' => $retry_ids, // Enviar los productos que deben ser reintentados
                'failed_ids' => $failed_ids, // Enviar los productos que fallaron permanentemente
                'synced' => $synced,
                'failed' => $failed,
                'state' => $state,
            ]);
        } catch (Exception $e) {
            $this->log_error("Error al modificar productos existentes: " . $e->getMessage());
            wp_send_json_error(__('Ocurrió un error al procesar los productos.', 'woo-ml-sync'));
        }
    }

    /**
     * Crea o actualiza el SKU de un producto en MercadoLibre.
     *
     * @param string $ml_product_id El ID del producto en MercadoLibre.
     * @return string|WP_Error Devuelve el ID del producto si la operación fue exitosa,
     *                         o un objeto WP_Error si ocurrió un error.
     */
    public function create_or_update_ml_product_sku($ml_product_id)
    {
        // Obtener producto de Mercadolibre
        $this->log_debug("***********************");
        $this->log_debug("Procesando producto con Mercadolibre ID: {$ml_product_id}");
        $ml_product = $this->obtener_producto_ml($ml_product_id);

        if (!$this->check_status($ml_product['status'])) {
            return false;
        }

        if ($ml_product['category_id'] != "MLC158385") {
            $this->log_debug("El producto no es un pijama. Producto omitido.");
            return false;
        }

        // Comprobación si el producto tiene variaciones en pijamas 
        if (!empty($ml_product['variations'])) {
            foreach ($ml_product['variations'] as $variation) {
                $variation_attributes = $variation['attributes'];
                $seller_sku_filter = array_filter($variation_attributes, fn($attr) => $attr['id'] === 'SELLER_SKU');
                $seller_sku = reset($seller_sku_filter);
                if ($seller_sku) {
                    $sku = $seller_sku['value_name'];
                    $this->log_debug("SKU encontrado en SELLER_SKU de la variación: " . $sku);
                    break;  // Detener el ciclo ya que ya hemos encontrado el SKU
                }
            }
        } else {
            $this->log_debug("El producto no tiene variaciones");
            $data['seller_custom_field'] = $ml_product['seller_custom_field'] ?? null;
        }

        // Verificar si se encontró un SKU de variación en MercadoLibre
        if (!empty($sku)) {
            $id_variacion_wc = wc_get_product_id_by_sku($sku) ?? null;
            if ($id_variacion_wc == null) {
                $data['seller_custom_field'] = $ml_product['id'];
                $this->log_debug("El producto no se encuentra en WooCommerce. Se le ha asignado como seller_custom_field su id de publicacion: " . $ml_product['id']);
            }
            $variacion_wc = wc_get_product($id_variacion_wc);
            $id_producto_wc = $variacion_wc->get_parent_id();
            $producto_wc = wc_get_product($id_producto_wc);
            $sku_wc = $producto_wc->get_sku();
            $data['seller_custom_field'] = $sku_wc;
            $this->log_debug("Asignando SKU del producto base como seller_custom_field: " . $sku_wc);
        }

        $endpoint_edit = WOO_ML_API_ENDPOINT . '/items/' . $ml_product_id;
        $update_response = $this->make_api_request_v2($endpoint_edit, 'PUT', $data);
        if (is_wp_error($update_response)) {
            $error_message = $update_response->get_error_message();
            $error_data = $update_response->get_error_data();
            $response_code = isset($error_data['status_code']) ? $error_data['status_code'] : null;

            if ($response_code === 429) {
                return new WP_Error('too_many_requests', 'Demasiadas solicitudes. Inténtalo más tarde.');
            } else if (strpos($error_message, 'under_review') !== false) {
                return new WP_Error('under_review', 'El producto está actualmente en revisión.');
            }

            return new WP_Error('update_error', $error_message);
        }
        $this->log_debug("Producto actualizado exitosamente");
        return $ml_product_id; // O el ID del producto actualizado
    }

    public function upload_product_to_mercadolibre()
    {
        $this->log_debug("🚀 Iniciando sincronización del producto con MercadoLibre");

        // Verifica Token
        if (!$this->check_and_refresh_token()) {
            $this->log_error("❌ No se pudo obtener un token de acceso válido.");
            wp_send_json_error("No se pudo obtener un token de acceso válido.");
        }

        // Prepara la data del producto
        $ml_product_data = $this->prepare_product_test();
        if (is_wp_error($ml_product_data)) {
            wp_send_json_error($ml_product_data->get_error_message());
        }

        // VALIDACIÓN DE METADATOS CRÍTICOS
        $woo_id = $ml_product_data['woo_id'] ?? 0;
        $producto = wc_get_product($woo_id);

        if (!$producto) {
            $this->log_error("❌ Producto de WooCommerce no encontrado con ID: $woo_id");
            wp_send_json_error("Producto de WooCommerce no encontrado.");
        }

        // Verifica categoría de ML
        $ml_category_id = get_post_meta($woo_id, '_ml_category_id', true);
        if (empty($ml_category_id)) {
            $this->log_error("❌ Falta el ID de categoría de MercadoLibre en el producto.");
            wp_send_json_error("Falta la categoría de MercadoLibre en el producto.");
        }

        // Verifica atributos obligatorios
        $ml_attributes = get_post_meta($woo_id, '_ml_attributes', true);
        if (empty($ml_attributes)) {
            $this->log_error("❌ El producto no tiene atributos de ML definidos.");
            wp_send_json_error("Faltan los atributos obligatorios para MercadoLibre.");
        }

        // Verifica imágenes
        if (empty($ml_product_data['pictures'])) {
            $this->log_error("❌ El producto no contiene imágenes para publicar en MercadoLibre.");
            wp_send_json_error("El producto debe tener al menos una imagen.");
        }

        $endpoint = WOO_ML_API_ENDPOINT . '/items';
        $method = 'POST';

        $response = $this->make_api_request($endpoint, $method, $ml_product_data);

        if (is_wp_error($response)) {
            $this->log_error('❌ Error al hacer request a MercadoLibre: ' . $response->get_error_message());
            wp_send_json_error('Error al sincronizar el producto: ' . $response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        // Manejo especial: body.invalid
        if ($status_code === 400 && isset($body['message']) && $body['message'] === 'body.invalid') {
            $titulo = $producto->get_name();
            $this->log_error("⚠️ Producto omitido por error body.invalid - WooID: $woo_id, Título: $titulo");

            if (!empty($body['cause'])) {
                foreach ($body['cause'] as $cause) {
                    $this->log_error("🔍 Causa body.invalid: " . print_r($cause, true));
                }
            }

            wp_send_json_error("MercadoLibre rechazó el producto por datos inválidos.");
            return;
        }

        // Manejo de éxito
        if ($status_code === 200 || $status_code === 201) {
            $this->log_debug("✅ Producto creado correctamente en MercadoLibre. ID: " . $body['id']);
            wp_send_json_success("Producto creado exitosamente con ID: " . $body['id']);
            return;
        }

        // Otros errores
        $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido', 'woo-ml-sync');
        $this->log_error("❌ Error inesperado. Código: $status_code. Mensaje: $error_message");

        if (isset($body['cause'])) {
            foreach ($body['cause'] as $cause) {
                $this->log_error("🔍 Causa detallada: " . print_r($cause, true));
            }
        }

        wp_send_json_error("Error al sincronizar el producto. Código: $status_code. Mensaje: $error_message");
    }

    private function prepare_product_test()
    {
        try {
            // Arregla la data
            $title = 'Ventilador Arctic P12 Pwm'; // Título de ejemplo
            $price = 15000; // Precio de ejemplo
            $available_quantity = 10; // Cantidad disponible de ejemplo
            $description = 'Ventilador Arctic P12 Pwm Pst 0db 120mm Blanco / Negro'; // Descripción de ejemplo
            $category_id = 'MLC9752'; // ID de categoría de ejemplo
            $seller_sku0 = 'skupadre'; // SKU de ejemplo
            $seller_sku1 = 'skuvariacion1'; // SKU de ejemplo
            $seller_sku2 = 'skuvariacion2'; // SKU de ejemplo
            $this->log_debug("La categoría del producto en mercadolibre es: {$category_id}");

            // Llama a la función atributosObligatorios
            $atributos_obligatorios = $this->atributosObligatorios($category_id);
            $this->log_debug("Atributos obligatorios: " . print_r($atributos_obligatorios, true));

            // Llama a la función inicializarAtributos
            $attributes = $this->inicializarAtributos($atributos_obligatorios);
            $this->log_debug("Atributos inicializados: " . print_r($attributes, true));

            // Arregla la data
            $data = array(
                'title' => $title,
                'category_id' => $category_id,
                'price' => $price,
                'currency_id' => 'CLP',
                'available_quantity' => $available_quantity,
                'buying_mode' => 'buy_it_now',
                'condition' => 'new',
                'seller_custom_field' => $seller_sku0,
                'listing_type_id' => 'gold_special',
                'description' => array('plain_text' => strip_tags($description)),
                'pictures' => array(
                    array('source' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8bogo3ugMNJxIyElKncQnp2zYGzLwb1Mm89g4COeUIsNK7bUpdiTZn2cB5l4BbtLc83U&usqp=CAU'),
                    array('source' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8bogo3ugMNJxIyElKncQnp2zYGzLwb1Mm89g4COeUIsNK7bUpdiTZn2cB5l4BbtLc83U&usqp=CAU')
                ), // Conjunto de imágenes de ejemplo
                'attributes' => $attributes,
                'seller_custom_field' => $seller_sku0,
            );

            // Verificar que los productos tengan Peso y Dimensión para poder agregar shipping
            $peso = 1; // Peso de ejemplo
            $largo = 10; // Largo de ejemplo
            $ancho = 10; // Ancho de ejemplo
            $alto = 10; // Alto de ejemplo

            $this->log_debug("Verificando Peso y Dimensión para agregar Shipping.");
            $this->log_debug("Peso: {$peso}");
            $this->log_debug("Largo: {$largo}");
            $this->log_debug("Ancho: {$ancho}");
            $this->log_debug("Alto: {$alto}");

            // Agrega datos de Shipping
            $data['shipping'] = array(
                'mode' => 'me2',
                'local_pick_up' => false,
                'free_shipping' => false,
                'dimensions' => "{$largo}x{$ancho}x{$alto},{$peso}"
            );
            $this->log_debug("Agregada data de shipping");

            // Crear variaciones para el producto
            $variations = array(
                array(
                    'price' => 15000,
                    'available_quantity' => 5,
                    'attribute_combinations' => array(
                        array(
                            'id' => 'LED_COLOR',
                            'name' => 'Color del LED',
                            'value_id' => '52049',
                            'value_name' => 'Negro',
                            'values' => array(
                                array(
                                    'id' => '52049',
                                    'name' => 'Negro',
                                    'struct' => null
                                )
                            ),
                            'value_type' => 'string'
                        )
                    ),
                    'sale_terms' => array(),
                    'picture_ids' => array('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8bogo3ugMNJxIyElKncQnp2zYGzLwb1Mm89g4COeUIsNK7bUpdiTZn2cB5l4BbtLc83U&usqp=CAU'),
                    'catalog_product_id' => 'MLC32624563',
                    'attributes' => array(
                        array(
                            'id' => 'SELLER_SKU',
                            'value_name' => $seller_sku1
                        )
                    )
                ),
                array(
                    'price' => 15000,
                    'available_quantity' => 5,
                    'attribute_combinations' => array(
                        array(
                            'id' => 'LED_COLOR',
                            'name' => 'Color del LED',
                            'value_id' => '52055',
                            'value_name' => 'Blanco',
                            'values' => array(),
                            'value_type' => 'string'
                        )
                    ),
                    'sale_terms' => array(),
                    'picture_ids' => array('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8bogo3ugMNJxIyElKncQnp2zYGzLwb1Mm89g4COeUIsNK7bUpdiTZn2cB5l4BbtLc83U&usqp=CAU'),
                    'catalog_product_id' => 'MLC36690272',
                    'attributes' => array(
                        array(
                            'id' => 'SELLER_SKU',
                            'value_name' => $seller_sku2
                        )
                    )
                )
            );
            $data['variations'] = $variations;
            $this->log_debug("Agregadas variaciones al producto");

            return $data;
        } catch (Exception $e) {
            $this->log_error("Error al preparar el producto de prueba: " . $e->getMessage());
            return new WP_Error('prepare_product_test_error', $e->getMessage());
        }
    }

    //Importa los precios de mercadolibre a WooCommerce
    public function return_sku()
    {
        $limit = 5;
        $status = "importando";
        $offset = $_POST['offset'] ?? 0;
        $productos_importados = $_POST['importados'] ?? 0;
        $sinsku = json_decode(stripslashes($_POST['sinsku']), true) ?? [];
        $SELLER_ID = $this->get_user_id();
        $CATEGORY_ID = "MLC158385";
        $endpoint = "https://api.mercadolibre.com/sites/MLC/search?seller_id=" . $SELLER_ID . "&category=" . $CATEGORY_ID . "&status=active&limit=" . $limit . "&offset=" . $offset;
        $response = $this->make_api_request_v2($endpoint, 'GET');
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $productos = $body['results'];
        $total_productos = $body['paging']['total'];

        $this->log_debug("Productos procesados: " . $offset . " de " . $total_productos);

        foreach ($productos as $producto) {
            $ml_product = $this->obtener_producto_ml($producto['id']);
            if ($ml_product['seller_custom_field'] == null) {
                $this->log_debug("Sin seller_custom_field: " . $ml_product['title']);
                $sinsku[] = $ml_product['title'];
            }
            $productos_importados++;
        }

        $this->log_debug("***");

        if ($productos_importados == $total_productos) {
            $status = "finalizado";
            $this->log_debug("Se han retornado con éxito todos los SELLER CUSTOM FIELD de la categoría PIJAMAS");
            $this->log_debug("Productos sin SELLER_CUSTOM_FIELD: " . print_r($sinsku, true));
        }

        // Respuesta final
        wp_send_json_success([
            'status' => $status,
            'offset' => $offset + $limit,
            'importados' => $productos_importados,
            'total' => $total_productos,
            'sinsku' => $sinsku,
        ]);
    }

    //Le asigna como valor a los atributos las opciones almacenadas
    public function load_settings()
    {
        $this->client_id = get_option('woo_ml_client_id');
        $this->client_secret = get_option('woo_ml_client_secret');
        $this->access_token = get_option('woo_ml_access_token');
        $this->refresh_token = get_option('woo_ml_refresh_token');
        $this->redirect_uri = admin_url('admin.php?page=woo-ml-sync');
    }

    //Crea las options de los atributos
    public function activate_plugin()
    {
        $options = array(
            'woo_ml_client_id',
            'woo_ml_client_secret',
            'woo_ml_access_token',
            'woo_ml_refresh_token',
            'woo_ml_token_expiration'
        );

        foreach ($options as $option) {
            if (!get_option($option)) {
                add_option($option, '');
            }
        }
    }

    //Crea el menu lateral
    public function add_admin_menu()
    {
        add_menu_page(
            __('WooCommerce MercadoLibre Sync', 'woo-ml-sync'),
            __('WooML Sync', 'woo-ml-sync'),
            'manage_options',
            'woo-ml-sync',
            array($this, 'admin_page'), //Ejecuta la función admin_page
            'dashicons-sync',
            56
        );
    }

    //Guarda en la base de datos woo_ml_options, las variables client_id y client_secret
    public function register_settings()
    {
        register_setting('woo_ml_options', 'woo_ml_client_id');
        register_setting('woo_ml_options', 'woo_ml_client_secret');
    }

    //Genera el link de la petición para obtener el código de autorización de mercadolibre
    //PKCE: Se añaden parámetros extras de seguridad: Code Verifier y Code challenge
    public function get_auth_url()
    {
        if (empty($this->client_id)) {
            return false;
        }
        $code_verifier = $this->generate_code_verifier();
        $code_challenge = $this->generate_code_challenge($code_verifier);
        update_option('woo_ml_code_verifier', $code_verifier);
        $state = bin2hex(random_bytes(16));
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'code_challenge' => $code_challenge,
            'code_challenge_method' => 'S256',
            'state' => $state
        );
        return add_query_arg($params, 'https://auth.mercadolibre.com/authorization');
    }

    //Método que genera el Code Verifier
    private function generate_code_verifier()
    {
        $random = random_bytes(32);
        return rtrim(strtr(base64_encode($random), '+/', '-_'), '=');
    }

    //Método que genera el Code Challenge
    private function generate_code_challenge($code_verifier)
    {
        $hashed = hash('sha256', $code_verifier, true);
        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    //Genera la página del plugin
    public function admin_page()
    {
        //Le asigna como valor a los atributos las opciones almacenadas
        $this->load_settings();

        //Según la información que reciba del POST, ejecuta diferentes funciones
        if (isset($_POST['woo_ml_save_credentials'])) {
            $this->save_credentials();
        }
        if (isset($_POST['woo_ml_verify_credentials'])) {
            $this->verify_credentials();
        }
        if (isset($_POST['ml_logout'])) {
            $this->logout();
        }
        if (isset($_POST['test_ml_connection'])) {
            $this->test_mercadolibre_connection();
        }

        //Genera la admin page
        include plugin_dir_path(__FILE__) . 'admin-page.php';
    }

    //Recibe las credenciales de POST, las guarda en Options y las asigna como valor de los atributos
    private function save_credentials()
    {
        if (!isset($_POST['woo_ml_credentials_nonce']) || !wp_verify_nonce($_POST['woo_ml_credentials_nonce'], 'woo_ml_save_credentials')) {
            $this->log_error('Error de seguridad al guardar las credenciales.');
            return;
        }
        $client_id = sanitize_text_field($_POST['woo_ml_client_id']);
        $client_secret = sanitize_text_field($_POST['woo_ml_client_secret']);
        update_option('woo_ml_client_id', $client_id);
        update_option('woo_ml_client_secret', $client_secret);
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        add_settings_error('woo_ml_messages', 'credentials_updated', __('Credenciales actualizadas con éxito.', 'woo-ml-sync'), 'success');
    }

    //Verifica las credenciales
    private function verify_credentials()
    {

        //Si no están estos datos en el POST es porque hay un error
        if (!isset($_POST['woo_ml_credentials_nonce']) || !wp_verify_nonce($_POST['woo_ml_credentials_nonce'], 'woo_ml_save_credentials')) {
            $this->log_error('Error de seguridad al verificar las credenciales.');
            return;
        }

        $client_id = $this->client_id;
        $client_secret = $this->client_secret;

        //Si los atributos están vacíos, hay un error
        if (empty($client_id) || empty($client_secret)) {
            add_settings_error('woo_ml_messages', 'credentials_empty', __('Por favor, ingrese el Client ID y Client Secret antes de verificar.', 'woo-ml-sync'), 'error');
            return;
        }

        //Se hace una consulta a OAuth de Mercadolibre para confirmar credenciales
        $response = wp_remote_post(WOO_ML_API_ENDPOINT . '/oauth/token', array(
            'body' => array(
                'grant_type' => 'client_credentials',
                'client_id' => $client_id,
                'client_secret' => $client_secret
            ),
            'timeout' => 30,
        ));

        //Verifica errores de credenciales
        if (is_wp_error($response)) {
            $this->log_error('Error al verificar las credenciales: ' . $response->get_error_message());
            add_settings_error('woo_ml_messages', 'credentials_error', __('Error al verificar las credenciales:', 'woo-ml-sync') . ' ' . $response->get_error_message(), 'error');
            return;
        }

        //Muestra los resultados
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);
        $this->log_debug('Respuesta de verificación de credenciales:');
        $this->log_debug('Código de estado: ' . $status_code);
        $this->log_debug('Cuerpo de la respuesta: ' . print_r($body, true));
        if ($status_code === 200 && isset($body['access_token'])) {;
            add_settings_error('woo_ml_messages', 'credentials_verified', __('Credenciales verificadas exitosamente.', 'woo-ml-sync'), 'success');
        } else {
            $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido', 'woo-ml-sync');
            $this->log_error('Error al verificar las credenciales: ' . $error_message);
            add_settings_error('woo_ml_messages', 'credentials_error', __('Error al verificar las credenciales:', 'woo-ml-sync') . ' ' . $error_message, 'error');
        }
    }

    //OAuth de Mercadolibre
    public function handle_oauth_response()
    {
        //Si no recibe el código de autorización via GET, es un error
        if (!isset($_GET['code'])) {
            return;
        }

        //Recibe el código de autorización
        $code = sanitize_text_field($_GET['code']);
        //Recibe el código verificador desde Options
        $code_verifier = get_option('woo_ml_code_verifier');

        //Verifica errores en el client id y el cliente secreto
        if (empty($this->client_id) || empty($this->client_secret)) {
            $this->log_error('Client ID o Client Secret no configurados correctamente.');
            add_settings_error('woo_ml_messages', 'oauth_error', __('Error: Client ID o Client Secret no configurados correctamente.', 'woo-ml-sync'), 'error');
            return;
        }

        //Envia la solicitd de Access Token
        $response = wp_remote_post(WOO_ML_API_ENDPOINT . '/oauth/token', array(
            'body' => array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $code,
                'redirect_uri' => $this->redirect_uri,
                'code_verifier' => $code_verifier
            ),
            'timeout' => 30,
        ));

        //Registra errores
        if (is_wp_error($response)) {
            $this->log_error('Error al conectar con MercadoLibre: ' . $response->get_error_message());
            add_settings_error('woo_ml_messages', 'oauth_error', __('Error al conectar con MercadoLibre:', 'woo-ml-sync') . ' ' . $response->get_error_message(), 'error');
            return;
        }

        //Recibe el token
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        //Verifica que exista el token
        if ($status_code === 200 && isset($body['access_token']) && isset($body['refresh_token'])) {
            //Guarda el token en Options
            update_option('woo_ml_access_token', $body['access_token']);
            update_option('woo_ml_refresh_token', $body['refresh_token']);
            update_option('woo_ml_token_expiration', time() + $body['expires_in']);
            //Asigna el token en los atributos
            $this->access_token = $body['access_token'];
            $this->refresh_token = $body['refresh_token'];
            add_settings_error('woo_ml_messages', 'oauth_success', __('Conexión exitosa con MercadoLibre', 'woo-ml-sync'), 'success');
            //Redirecciona con información GET
            wp_redirect(admin_url('admin.php?page=woo-ml-sync&ml_connected=1'));
            exit;
        } else {
            $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido en la respuesta de MercadoLibre', 'woo-ml-sync');
            $this->log_error('Error en la respuesta de MercadoLibre: ' . $error_message);
            add_settings_error('woo_ml_messages', 'oauth_error', __('Error en la respuesta de MercadoLibre:', 'woo-ml-sync') . ' ' . $error_message, 'error');
        }
    }

    //Agrega los enlaces de ajustes.
    // public function agregar_enlace_ajustes($enlaces)
    // {
    //     $enlace_ajustes = '<a href="' . admin_url('admin.php?page=woo-ml-sync') . '">' . __('Ajustes', 'woo-ml-sync') . '</a>';
    //     array_unshift($enlaces, $enlace_ajustes);
    //     return $enlaces;
    // }

    private function verificar_data_y_token($data)
    {
        if (!$data || !$this->check_and_refresh_token()) {
            $this->log_error(
                !$data
                    ? "No se pudo obtener el producto"
                    : "No se pudo obtener un token de acceso válido."
            );
            return false;
        }
        return true;
    }

    private function check_status($status)
    {
        $estados = ['closed', 'under_review', 'inactive', null];
        if (in_array($status, $estados)) {
            $this->log_debug("El producto está " . $status . " en MercadoLibre.");
            return false;
        }
        return true;
    }

    private function buscar_en_ml_por_sku($sku)
    {
        $this->log_debug("Buscando producto en MercadoLibre por SKU de WooCommerce: " . $sku);
        $ml_product_id = $this->get_simple_product_ml_id_by_sku($sku);
        if ($ml_product_id) {
            $this->log_debug("ID de MercadoLibre encontrado: " . $ml_product_id);
            return $ml_product_id;
        }
        return false;
    }

    //Crea y/o actualiza un producto en Mercadolibre si existe en WooCommerce
    public function sync_product_to_mercadolibre($product)
    {
        if (!$this->verificar_data_y_token($product)) {
            return false;
        }
        $this->log_debug("-----------------");
        $this->log_debug("Sincronizando producto con ID de WooCommerce: " . $product->get_id());
        //Obtiene la id de mercadolibre, verificar que existan los
        //$ml_product_id = get_post_meta($product->get_id(), '_mercadolibre_id', true); //Obtiene el ID de Mercadolibre de los metadatos
        //$this->log_debug("ID de Mercadolibre asociado: " . $ml_product_id);
        $ml_product_id = $this->buscar_en_ml_por_sku($product->get_sku());
        try {
            if ($ml_product_id) {
                $producto_ml = $this->obtener_producto_ml($ml_product_id);
                $this->log_debug("Producto obtenido de mercadolibre");
                if (!$this->check_status($producto_ml['status'])) {
                    return false;
                }
                $ml_product_data = $this->prepare_product_update_data($product, $producto_ml);
                $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_product_id";
                $method = 'PUT';
            } else {
                //Si el producto no existe en mercadolibre, prepara la data para crear el producto
                $ml_product_data = $this->prepare_product_data($product);
                $endpoint = WOO_ML_API_ENDPOINT . '/items';
                $method = 'POST';
            }

            //$this->log_debug("Datos del producto a enviar: " . print_r($ml_product_data, true));

            //Hace una petición enviando al endpoint la data del producto
            try {
                $response = $this->make_api_request_v2($endpoint, $method, $ml_product_data);
                if (is_wp_error($response)) {
                    $this->log_error("Problemas al crear o actualizar producto");
                    throw new Exception('Error al sincronizar el producto con MercadoLibre: ' . $response->get_error_message());
                }
            } catch (Throwable $e) {
                $this->log_error("Error al obtener información de la grilla de tallas: " . $e->getMessage());
                return false;
            }

            //Resultado de la petición
            $body = json_decode(wp_remote_retrieve_body($response), true);
            $status_code = wp_remote_retrieve_response_code($response);

            if ($status_code === 200 || $status_code === 201) {
                $this->log_debug("Nuevo producto creado o modificado en MercadoLibre con ID: " . $body['id']);
                //Actualiza los metadatos del producto
                update_post_meta($product->get_id(), '_mercadolibre_id', $body['id']);
                update_post_meta($product->get_id(), '_datos_mercadolibre', $body);

                return $body['id']; //En caso de éxito, se retorna el nuevo ID de Mercadolbire por si se llama desde una sincronización de stock
            } else {
                $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido', 'woo-ml-sync');
                throw new Exception("Error al sincronizar el producto. Código de estado: $status_code. Mensaje: $error_message");
            }
        } catch (Exception $e) { //Atrapa errores
            $this->log_error($e->getMessage());

            if (isset($body['cause'])) {
                foreach ($body['cause'] as $cause) {
                    $this->log_error("Causa detallada: " . print_r($cause, true));
                }
            }

            return false;
        }
    }

    /**
     * @param WC_Product_Variation $variation
     *
     * @return void
     */
    public function sync_variation_stock_to_mercadolibre($variation)
    {
        $this->log_debug("********************");
        $this->log_debug("Se ha registrado una venta en WooCommerce de la variacion " . $variation->get_id() . ". Actualizando stock en Mercadolibre.");

        $search_sku = $variation->get_sku();
        $parent_id = $variation->get_parent_id();

        if (! $parent_id || ! $search_sku) {
            $this->log_error("La variación en WooCommerce no tiene SKU.");
            $this->log_debug("********************");
            return;
        }

        //Si el producto no tiene id de mercadolibre asociado, lo sincroniza
        $ml_product_id = get_post_meta($parent_id, '_mercadolibre_id', true);
        if (!$ml_product_id) {
            $this->log_debug("El producto ID: " . $parent_id . " no está sincronizado con MercadoLibre. Comenzando sincronización...");
            $ml_product_id = $this->sync_product_to_mercadolibre($parent_id);
            if (!$ml_product_id) {
                $this->log_debug("No se pudo sincronizar el producto con Mercadolibre.");
                $this->log_debug("********************");
                return;
            }
        }

        $id = $this->get_variation_ml_id_by_sku($ml_product_id, $search_sku);
        if (is_wp_error($id)) {
            $this->log_debug('No se encontró la variación en Mercadolibre por SKU');
            $this->log_debug("********************");
            return;
        }

        $data = ['id' => $id, 'available_quantity' => $variation->get_stock_quantity(),];
        $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_product_id/variations/$id";
        $response = $this->make_api_request_v2($endpoint, 'PUT', $data);
        if (is_wp_error($response)) {
            $this->log_debug('Error al sincronizar el stock de la variacion de WooCommerce con MercadoLibre: [CAUSA] ' . json_encode($response));
        } else {
            $this->log_debug("Stock actualizado en MercadoLibre");
        }

        $this->log_debug("********************");
    }

    public function sync_stock_to_mercadolibre($product)
    {
        $this->log_debug("********************");
        $this->log_debug("Se ha registrado una venta en WooCommerce del producto " . $product->get_id() . ". Actualizando stock en Mercadolibre.");

        if ($product->is_type('variable')) {
            $this->log_debug("Este Hook no debiese activarse para productos variables.");
            $this->log_debug("********************");
            return;
        }

        // ✅ Validar metadatos ANTES de sincronizar
        $errores = $this->validar_metadatos_producto($product);
        if (!empty($errores)) {
            $this->log_debug('Errores en producto ID ' . $product->get_id() . ': ' . implode('; ', $errores));
            $this->log_debug("********************");
            return;
        }

        // Si el producto no tiene ID de MercadoLibre asociado, sincronízalo
        $ml_product_id = get_post_meta($product->get_id(), '_mercadolibre_id', true);
        if (!$ml_product_id) {
            $this->log_debug("El producto ID: " . $product->get_id() . " no está sincronizado con MercadoLibre. Comenzando sincronización...");
            $ml_product_id = $this->sync_product_to_mercadolibre($product);
            if (!$ml_product_id) {
                $this->log_debug("No se pudo sincronizar el producto con Mercadolibre.");
                $this->log_debug("********************");
                return;
            }
        }

        // Captura stock del producto en WooCommerce
        $stock = $product->get_stock_quantity();

        $data = array(
            'available_quantity' => $stock ? intval($stock) : 0
        );

        // 📋 Registrar en el log la data enviada
        $this->log_debug('Cuerpo del request a MercadoLibre (stock): ' . json_encode($data));

        // Envía la data del stock de WooCommerce a la API de MercadoLibre
        $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_product_id";
        $response = $this->make_api_request_v2($endpoint, 'PUT', $data);

        if (is_wp_error($response)) {
            $this->log_error("Error al actualizar el stock en MercadoLibre para el producto ID: $product->get_id()");
        } else {
            $this->log_debug("Stock actualizado en MercadoLibre para el producto ID: $product->get_id()");
        }

        $this->log_debug("********************");
    }

    private function validar_metadatos_producto($product) {
    $errores = array();

    if (!$product->get_sku()) {
        $errores[] = 'SKU faltante.';
    }

    if ($product->get_price() < WOO_ML_MIN_PRICE) {
        $errores[] = 'Precio por debajo del mínimo permitido.';
    }

    if ($product->get_stock_quantity() === null) {
        $errores[] = 'Stock faltante.';
    }

    if (!$product->get_name()) {
        $errores[] = 'Nombre faltante.';
    }

    if (!$product->get_description()) {
        $errores[] = 'Descripción faltante.';
    }

    if (empty($product->get_category_ids())) {
        $errores[] = 'Categoría faltante.';
    }

    if (empty($product->get_gallery_image_ids())) {
        $errores[] = 'Imágenes faltantes.';
    }

    return $errores;
    }

    //Asigna valor de WooCommerce al producto de Mercadolibre
    // public function sync_price_to_mercadolibre($product_id, $price, $price_type)
    // {
    //     //Obtenemos el producto de mercadolibre
    //     $ml_product_id = get_post_meta($product_id, '_mercadolibre_id', true);

    //     //Si el producto no existe en mercadolibre, nah que hacer
    //     if (!$ml_product_id) {
    //         $this->log_debug("El producto ID: $product_id no está sincronizado con MercadoLibre.");
    //         return;
    //     }

    //     $this->log_debug("Intentando actualizar precio para producto ID: $product_id, ML ID: $ml_product_id, Nuevo precio: $price");
    //     //Almacena la data del precio de WooCommerce y lo envia a la API de mercadolibre
    //     $data = array(
    //         'price' => max(floatval($price), WOO_ML_MIN_PRICE)
    //     );

    //     $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_product_id";
    //     $response = $this->make_api_request_v2($endpoint, 'PUT', $data);

    //     //Captura error
    //     if (is_wp_error($response)) {
    //         $this->log_error("Error al actualizar el precio en MercadoLibre para el producto ID: $product_id");
    //     } else {
    //         //Respuesta
    //         $body = json_decode(wp_remote_retrieve_body($response), true);
    //         $status_code = wp_remote_retrieve_response_code($response);

    //         if ($status_code === 200) {
    //             $this->log_debug("Precio actualizado en MercadoLibre para el producto ID: $product_id");
    //         } else {
    //             $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido', 'woo-ml-sync');
    //             $this->log_error("Error al actualizar el precio en MercadoLibre para el producto ID: $product_id. Código: $status_code. Mensaje: $error_message");
    //         }
    //     }
    // }

    //Chequeo y refresco de token
    private function check_and_refresh_token()
    {
        if (empty($this->access_token) || $this->is_token_expired()) {
            return $this->refresh_access_token();
        }
        return true;
    }

    //Ver la token_expiration
    private function is_token_expired()
    {
        $token_expiration = get_option('woo_ml_token_expiration');
        return !$token_expiration || $token_expiration < time();
    }

    //Refresca el Access Token
    public function refresh_access_token()
    {
        //Verifica que exista el refresh token
        if (!$this->refresh_token) {
            $this->log_error('No hay refresh token disponible');
            return false;
        }

        //Manda la consulta por refresh_token
        $response = wp_remote_post(WOO_ML_API_ENDPOINT . '/oauth/token', array(
            'body' => array(
                'grant_type' => 'refresh_token',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'refresh_token' => $this->refresh_token
            ),
            'timeout' => 30,
        ));

        //Registra el error
        if (is_wp_error($response)) {
            $this->log_error('Error al refrescar el token: ' . $response->get_error_message());
            return false;
        }

        //Respuesta
        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (isset($body['access_token']) && isset($body['refresh_token'])) {
            //Si existe el access token y el refresh token, los guarda en Options y en los atributos
            update_option('woo_ml_access_token', $body['access_token']);
            update_option('woo_ml_refresh_token', $body['refresh_token']);
            update_option('woo_ml_token_expiration', time() + $body['expires_in']);
            $this->access_token = $body['access_token'];
            $this->refresh_token = $body['refresh_token'];
            $this->log_debug('Token refrescado exitosamente');
            return true;
        } else {
            $this->log_error('Error en la respuesta al refrescar el token: ' . print_r($body, true));
            return false;
        }
    }

    //Convierte la data de WooCommerce a la de Mercadolibre
    //***NOTA IMPORTANTE:
    // Por alguna razón, los metadatos vienen con valores fuera de lo permitido por Mercadolibre en values.
    // Para estos casos, simplemente se está eliminando el atributo. */
private function prepare_product_data($product)
{
    $this->log_debug("Producto no encontrado en Mercadolibre.");
    $this->log_debug("** CREACION DE PRODUCTO **");

    //Obtenemos los metadatos del producto
    $metadatos = get_post_meta($product->get_id(), '_datos_mercadolibre', true);
    if (empty($metadatos)) {
        $this->log_debug("No se encontraron metadatos del producto en WooCommerce.");
        return false;
    }

    //Obtiene la descripción del producto
    $description = $product->get_description();
    if (empty($description)) {
        $description = $product->get_short_description();
    }

    //Obtiene el precio desde Mercado Libre (si existe), si no, de WooCommerce
    $precio_mercadolibre = $metadatos['price'] ?? null;

    if ($precio_mercadolibre !== null && floatval($precio_mercadolibre) > 0) {
        $price = floatval($precio_mercadolibre);
        $this->log_debug("Usando precio desde MercadoLibre: $price");
    } else {
        $price = floatval($product->get_price());
        $this->log_debug("Usando precio desde WooCommerce: $price");

        if ($price <= 0) {
            $price = WOO_ML_MIN_PRICE;
            $this->log_debug("Precio inválido, se asigna el precio mínimo: " . WOO_ML_MIN_PRICE);
        }
    }

    //Obtiene la categoría de mercadolibre
    $category_id = $metadatos['category_id'] ?? null;
    if (!$category_id) {
        $this->log_debug("No se encontró la categoría del producto en los metadatos.");
        return false;
    }

    //Encontrar atributos obligatorios por categoria para no eliminarlos
    $endpoint = "https://api.mercadolibre.com/categories/" . $category_id . "/attributes";
    $response = $this->make_api_request_v2($endpoint, 'GET');
    $atributos_categoria = json_decode(wp_remote_retrieve_body($response), true);
    $atributos_obligatorios = [];
    foreach ($atributos_categoria as $atr) {
        if (in_array('required', $atr['tags']) || in_array('conditional_required', $atr['tags'])) {
            $atributos_obligatorios[] = $atr['id'];
        }
    }

    //Obtiene los atributos del producto almacenados en WooCommerce
    $attributes = $metadatos['attributes'];

    $this->log_debug("Borrando atributos incompletos en Metadatos");
    foreach ($attributes as $key => $atributo) {
        if ($atributo["value_id"] === null || $atributo["value_id"] == "" || empty($atributo["value_id"])) {
            if (!in_array($atributo['id'], $atributos_obligatorios)) {
                unset($attributes[$key]);
                $this->log_debug("Se ha eliminado el atributo " . $atributo['id']);
            }
        }

        // Atributos innecesarios
        $atributos_a_eliminar = [
            "IS_TOM_BRAND", "FILTRABLE_SIZE", "FILTRABLE_GENDER", "AGE_GROUP",
            "IS_HIGHLIGHT_BRAND", "TOP_TYPES", "SIZE_GRID_ID", "PAJAMA_BOTTOM_TYPES", "SLEEVE_TYPES"
        ];
        if (in_array($atributo["id"], $atributos_a_eliminar)) {
            unset($attributes[$key]);
        }

        if ($atributo["id"] === "SIZE_GRID_ID") {
            $this->log_debug("Atributos referentes a la guia de tallas eliminados");
        }
    }

    $attributes = array_values($attributes); // Reindexar

    //Verificar si el producto es de moda
    $dominio = $this->get_product_domain($category_id);
    $size_grid_id = false;
    $user_id = $this->get_user_id();
    if ($this->check_domain($dominio)) {
        $tallas_wc = array_map(function ($talla) {
            return str_replace('-', '/', strtoupper($talla));
        }, $product->get_variation_attributes()['pa_talla']);

        $size_grid_id = $this->encontrar_size_grid($metadatos, $dominio, $user_id, $tallas_wc) ?? $this->crear_size_grid($metadatos, $dominio);
        $this->log_debug("Guia de tallas a asignar: " . $size_grid_id);
        $attributes[] = [
            'id' => "SIZE_GRID_ID",
            'value_name' => $size_grid_id,
        ];
    }

    $data = array(
        'title' => $product->get_name(),
        'category_id' => $category_id,
        'price' => $price,
        'currency_id' => 'CLP',
        'available_quantity' => $product->get_stock_quantity() ? intval($product->get_stock_quantity()) : 1,
        'buying_mode' => 'buy_it_now',
        'condition' => 'new',
        'listing_type_id' => 'gold_special',
        'description' => array('plain_text' => strip_tags($description)),
        'pictures' => $metadatos['pictures'],
        'attributes' => $attributes,
        'seller_custom_field' => $product->get_sku(),
    );

    if ($product->is_type('variable')) {
        $data['variations'] = $this->prepare_variations($product, $metadatos, $size_grid_id);
    } else {
        $this->log_debug("Producto Simple");
        $data['attributes'] = [
            [
                'id' => 'SELLER_SKU',
                'name' => 'SKU',
                'value_name' => $product->get_sku()
            ]
        ];
    }

    $this->add_shipping_mode($data);

    $this->log_debug("Data lista para crear producto en Mercadolibre");
    return $data;
}

    private function actualizar_variaciones($product, $producto_ml, $metadatos, $size_grid_id)
    {
        $this->log_debug("Actualizando variaciones");

        // Arreglo de variaciones preparadas
        $prepared_variations = array();
        $variations = $product->get_available_variations();
        $variation_attributes = $product->get_variation_attributes();
        foreach ($variations as $variacion_wc) {
            // Capturar la variación de WooCommerce y almacenar su data
            $variation_product = wc_get_product($variacion_wc['variation_id']);
            if (!$variation_product || !is_object($variation_product)) {
                $this->log_debug("El objeto 'variation_product' no es válido.");
                continue; // Saltar esta iteración si el producto no es válido
            }

            // Obtener todos los atributos de la variación
            $variation_attributes = $variation_product->get_attributes();

            // Obtener SKU de la variación
            $variation_sku = $variation_product->get_sku();
            $variation_data = array(
                'price' => max(floatval($variation_product->get_price()), WOO_ML_MIN_PRICE),
                'available_quantity' => intval($variation_product->get_stock_quantity()),
                'attribute_combinations' => array(), //De Metadatos (menos la guia de tallas)
                'picture_ids' => array() //De Woo Commerce
            );

            //Eliminar de los metadatos los atributos referentes a la guia de tallas
            $encontrado = false;
            foreach ($metadatos['variations'] as $variation) {
                if ($encontrado) {
                    break;
                }
                //Talla para SIZE_GRID_ROW_ID
                $size = array_column($variation['attribute_combinations'], 'value_name', 'id')['SIZE'] ?? null;
                foreach ($variation['attributes'] as $atributo) {
                    //Variacion en Metadatos
                    if ($atributo['id'] == 'SELLER_SKU' && $atributo['value_name'] == $variation_sku) {
                        $sku_meta = $atributo['value_name'];
                        //Variacion en Mercadolibre
                        foreach ($producto_ml['variations'] as $ml_variacion) {
                            $sku_ml = array_column($ml_variacion['attributes'], 'value_name', 'id')['SELLER_SKU'] ?? null;
                            if ($sku_meta == $sku_ml) {
                                //Si la variacion existe, se conserva su ID
                                $variation_data['id'] = $ml_variacion['id'];
                                break;
                            }
                        }

                        //Se eliminan los atributos de size_grid de la data
                        $variation_data['attributes'] = $variation['attributes'];
                        foreach ($variation_data['attributes'] as $key => $atributo) {
                            if ($atributo["id"] == "FILTRABLE_SIZE") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                            }
                            if ($atributo["id"] == "SIZE_GRID_ROW_ID") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                            }
                            if ($atributo["id"] == "EMPTY_GTIN_REASON") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                            }
                            if ($atributo["id"] == "SELLER_SKU") {
                                // Eliminar el elemento del array
                                unset($variation_data['attributes'][$key]);
                            }
                        }

                        // Reindexar el array para evitar que se convierta en objeto JSON
                        $variation_data['attributes'] = array_values($variation_data['attributes']);

                        //añadir el atributo SIZE_GRID_ROW_ID a variation_data
                        if ($size_grid_id) {
                            $size_grid_row_id = $this->get_valid_size_grid_row_id($size_grid_id, $size);
                            $variation_data['attributes'][] = array(
                                'id' => 'SIZE_GRID_ROW_ID',
                                'value_name' => $size_grid_row_id,
                            );
                        }

                        //añadir el atributo seller_sku a variation_data
                        $variation_data['attributes'][] = array(
                            'id' => 'SELLER_SKU',
                            'value_name' => $variation_sku,
                        );

                        $variation_data['attribute_combinations'] = $variation['attribute_combinations'];
                        $variation_data['picture_ids'] = $variation['picture_ids'];
                        $encontrado = true;
                    }
                }
            }

            $prepared_variations[] = $variation_data;
        }
        //$this->log_debug("Prepared-Variations: " . print_r($prepared_variations, true));
        $this->log_debug("Variaciones preparadas para actualización en MercadoLibre");
        return $prepared_variations;
    }

    private function prepare_variations($product, $metadatos, $size_grid_id)
    {
        $this->log_debug("Preparando variaciones para actualización en MercadoLibre");

        // Arreglo de variaciones preparadas
        $prepared_variations = array();
        $variations = $product->get_available_variations();
        $variation_attributes = $product->get_variation_attributes();

        foreach ($variations as $variation) {
            // Capturar la variación de WooCommerce y almacenar su data
            $variation_product = wc_get_product($variation['variation_id']);
            if (!$variation_product || !is_object($variation_product)) {
                $this->log_debug("El objeto 'variation_product' no es válido.");
                continue; // Saltar esta iteración si el producto no es válido
            }

            // Obtener todos los atributos de la variación
            $variation_attributes = $variation_product->get_attributes();

            // Obtener SKU de la variación
            $variation_sku = $variation_product->get_sku();
            $variation_data = array(
                'price' => max(floatval($variation_product->get_price()), WOO_ML_MIN_PRICE),
                'available_quantity' => intval($variation_product->get_stock_quantity()),
                'attribute_combinations' => array(),
                'picture_ids' => array()
            );

            //Eliminar de los metadatos los atributos referentes a la guia de tallas
            foreach ($metadatos['variations'] as $variation) {
                $size = array_column($variation['attribute_combinations'], 'value_name', 'id')['SIZE'] ?? null;
                foreach ($variation['attributes'] as $atributo) {
                    if ($atributo['id'] == 'SELLER_SKU' && $atributo['value_name'] == $variation_sku) {
                        $variation_data['attributes'] = $variation['attributes'];
                        foreach ($variation_data['attributes'] as $key => $atributo) {
                            if ($atributo["id"] == "FILTRABLE_SIZE") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                                $this->log_debug("Eliminando atributos referentes a la guia de tallas para la variacion " . $variation['id']);
                            }
                            if ($atributo["id"] == "SIZE_GRID_ROW_ID") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                            }
                            if ($atributo["id"] == "EMPTY_GTIN_REASON") {
                                unset($variation_data['attributes'][$key]); // Eliminar el elemento del array
                            }
                            if ($atributo["id"] == "SELLER_SKU") {
                                // Eliminar el elemento del array
                                unset($variation_data['attributes'][$key]);
                            }
                        }

                        // Reindexar el array para evitar que se convierta en objeto JSON
                        $variation_data['attributes'] = array_values($variation_data['attributes']);

                        if ($size_grid_id) {
                            $size_grid_row_id = $this->get_valid_size_grid_row_id($size_grid_id, $size);
                            $variation_data['attributes'][] = array(
                                'id' => 'SIZE_GRID_ROW_ID',
                                'value_name' => $size_grid_row_id,
                            );
                        }

                        //añadir el atributo seller_sku a variation_data
                        $variation_data['attributes'][] = array(
                            'id' => 'SELLER_SKU',
                            'value_name' => $variation_sku,
                        );

                        $variation_data['attribute_combinations'] = $variation['attribute_combinations'];
                        $variation_data['picture_ids'] = $variation['picture_ids'];
                    }
                }
            }

            $prepared_variations[] = $variation_data;
        }
        //$this->log_debug("Prepared-Variations: " . print_r($prepared_variations, true));
        $this->log_debug("Variaciones preparadas para actualización en MercadoLibre");
        return $prepared_variations;
    }

    //*********** NOTA IMPORTANTE  ***************/
    // Cuando el ítem tiene ventas, no puedes cambiar: Título, Modo de compra, Métodos de Pago distintos de Mercado Pago
    // Cuando el ítem está activo puedes modificar: Available_quantity, Precio, Video, Imágenes, Descripción, Envío
    // Precio y descripcion dieron problemas, asi que no se estan modificando
    private function prepare_product_update_data($producto_wc, $producto_ml)
    {
        $this->log_debug("** ACTUALIZACION DE PRODUCTO **");
        $metadatos = get_post_meta($producto_wc->get_id(), '_datos_mercadolibre', true);
        if (empty($metadatos)) {
            $this->log_debug("No se encontraron metadatos del producto en WooCommerce.");
            return false;
        }

        $this->log_debug("Categoria: " . $metadatos['category_id']);

        $data['pictures'] = $metadatos['pictures'];

        $data = [
            'seller_custom_field' => $producto_wc->get_sku(),
        ];
        $this->log_debug("Seller_custom_field(SKU) añadido");

        // Si el producto no tiene ventas, modifica el título
        if ($producto_ml['sold_quantity'] == 0) {
            $data['title'] = $metadatos['title'];
            $this->log_debug("Título añadido");
        }

        //Verificar si el producto es de moda
        $dominio = $this->get_product_domain($metadatos['category_id']);
        $size_grid_id = false;
        $user_id = $this->get_user_id();
        if ($this->check_domain(($dominio))) {
            //Obtener tallas de WooCommerce, pasando a mayúsculas los valores y cambiando guiones por slash
            $tallas_wc = array_map(function ($talla) {
                return str_replace('-', '/', strtoupper($talla));
            }, $producto_wc->get_variation_attributes()['pa_talla']);
            $size_grid_id = $this->encontrar_size_grid($metadatos, $dominio, $user_id, $tallas_wc) ?? $this->crear_size_grid($metadatos, $dominio);
            $data['attributes'][] = [
                'id' => "SIZE_GRID_ID",
                'value_name' => $size_grid_id,
            ];
        } else {
            $this->log_error("EL DOMINIO NO ESTÁ ACTIVO");
        }

        // Agregar variaciones o datos de producto simple
        if ($producto_wc->is_type('variable')) {
            $data['variations'] = $this->actualizar_variaciones($producto_wc, $producto_ml, $metadatos, $size_grid_id);
        } else {
            $data += [
                'available_quantity' => $producto_wc->get_stock_quantity() ? intval($producto_wc->get_stock_quantity()) : 1,
                'pictures'           => $this->get_product_images($producto_wc),
            ];
        }
        $this->log_debug("Data lista para la actualización");
        return $data;
    }
    //ඞ
    //Retorna los atributos que son obligatorios para la categoría
    private function atributosObligatorios($category_id)
    {
        $this->log_debug("Buscando atributos obligatorios por categoría de Mercadolibre {$category_id}");
        $endpoint = WOO_ML_API_ENDPOINT . "/categories/{$category_id}/attributes";
        $response = $this->make_api_request_sinLog($endpoint, 'GET');

        if (is_wp_error($response)) {
            return false;
            $this->log_debug("Error al obtener los atributos de la categoría");
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data)) {
            return false;
        }

        // Filtrar los atributos obligatorios
        $atributosTags = array_filter($data, function ($attribute) {
            return $attribute['tags']['required'] ?? false;
        });

        $this->log_debug("Se han encontrado los siguientes atributos obligatorios por categoría de Mercadolibre:");
        foreach ($atributosTags as $atributo) {
            $value = $atributo['id'];
            $this->log_debug("Atributo: {$value}");
        }

        return $atributosTags;
    }

    //Inicializar la data
    private function inicializarAtributos($atributosObligatorios)
    {
        foreach ($atributosObligatorios as $atributo) {
            if ($atributo['values'] != null) //Si tiene valores asociados, se asigna el primer valor
            {
                $atributo['value_id'] = $atributo['values'][0]['id'];
                $atributo['value_name'] = $atributo['values'][0]['name'];
                $attributes[] = $atributo;
                $idTor = $atributo['id'];
                $valueTor = $atributo['values'][0]['name'];;
                $this->log_debug("Se ha creado el atributo con valor asociado {$idTor} con el valor {$valueTor}");
            } else //Si no tiene valores asociados, se asigna un valor genérico
            {
                $atributo['value_name'] = "Genérico";
                $attributes[] = $atributo;
                $id = $atributo['id'];
                $value = $atributo['value_name'];
                $this->log_debug("Se ha creado el atributo {$id} con el valor {$value}");
            }
        }
        return $attributes;
    }

    //Obtiene los atributos del producto agregándole Marca y Modelo
    // private function get_product_attributes($product)
    // {
    //     $attributes = array();
    //     $product_attributes = $product->get_attributes();

    //     foreach ($product_attributes as $attribute) {
    //         if ($attribute->get_variation()) {
    //             // Skip variation attributes as they will be handled in variations
    //             //Los atributos de las variaciones tiene su método especial
    //             continue;
    //         }
    //         //Obtiene los atributos de WooCommerce
    //         if ($attribute->is_taxonomy()) {
    //             $attribute_taxonomy = $attribute->get_taxonomy_object();
    //             $attribute_values = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
    //             $attributes[] = array(
    //                 'id' => $attribute_taxonomy->attribute_name,
    //                 'name' => wc_attribute_label($attribute->get_name()),
    //                 'value_name' => implode(', ', $attribute_values),
    //             );
    //         } else {
    //             $attributes[] = array(
    //                 'id' => $attribute->get_name(),
    //                 'name' => $attribute->get_name(),
    //                 'value_name' => implode(', ', $attribute->get_options()),
    //             );
    //         }
    //     }

    //     $this->log_debug("Buscando atributos guardados en WooCommerce.");
    //     if (empty($attributes)) {
    //         $this->log_debug("El producto no tiene atributos guardados en WooCommerce.");
    //     } else {
    //         foreach ($attributes as $atributito) {
    //             $nombre = $atributito['name'];
    //             $valor = $atributito['value_name'];
    //             $this->log_debug("desde get_product_attributes");
    //             $this->log_debug("{$nombre}: {$valor}");
    //         }
    //     }

    //     return $attributes;
    // }

    //Consulta si el producto en WooCommerce tiene una categoría de mercadolibre asociada
    //Sino, se la asigna
    // private function get_mercadolibre_category($product)
    // {
    //     $category_id = get_post_meta($product->get_id(), '_mercadolibre_category_id', true);
    //     if (!$category_id) {
    //         $category_id = $this->find_best_category($product);
    //     }
    //     return $category_id;
    // }

    //Asigna una categoría de Mercadolibre enviandole el nombre del producto a la API de Mercadolibre. Si no lo logra, le asigna una genérica.
    private function find_best_category($product)
    {
        $this->log_debug("Encontrando la mejor categoría para el producto");
        $woo_categories = wp_get_post_terms($product->get_id(), 'product_cat', array('fields' => 'names'));

        $product_name = $product->get_name();
        $search_terms = implode(' ', array_merge($woo_categories, array($product_name)));

        $endpoint = WOO_ML_API_ENDPOINT . '/sites/MLC/domain_discovery/search?q=' . urlencode($search_terms);
        $response = $this->make_api_request_v2($endpoint, 'GET');

        if (is_wp_error($response)) {
            $this->log_error('Error al buscar la mejor categoría: ' . $response->get_error_message());
            return 'MLC1276'; // Categoría genérica como fallback
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($body) && isset($body[0]['category_id'])) {
            return $body[0]['category_id'];
        }

        return 'MLC1276';
    }

    //Obtiene el conjunto de imágenes
    private function get_product_images($product)
    {
        $images = array();
        $attachment_ids = $product->get_gallery_image_ids();

        if ($product->get_image_id()) {
            array_unshift($attachment_ids, $product->get_image_id());
        }

        foreach ($attachment_ids as $attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            if ($image_url) {
                $images[] = array('source' => $image_url);
            }
        }

        return $images;
    }

    //Prepara la data de las variaciones y sus atributos
    // private function prepare_variation_data($variation)
    // {
    //     $parent_product = wc_get_product($variation->get_parent_id());
    //     $variation_data = array(
    //         'price' => max(floatval($variation->get_price()), WOO_ML_MIN_PRICE),
    //         'available_quantity' => intval($variation->get_stock_quantity()),
    //         'attribute_combinations' => array(),
    //     );

    //     $attributes = $variation->get_attributes();
    //     foreach ($attributes as $attribute_name => $attribute_value) {
    //         if (!empty($attribute_value)) {
    //             $attribute_label = wc_attribute_label($attribute_name, $parent_product);
    //             $variation_data['attribute_combinations'][] = array(
    //                 'name' => $attribute_label,
    //                 'value_name' => $attribute_value,
    //             );
    //         }
    //     }

    //     return $variation_data;
    // }

    //Importar un producto de mercadolibre ($ml_id)
    public function importarProductoDesdeMercadolibre($ml_id)
    {
        $this->log_debug("************");
        $this->log_debug("Importando a WooCommerce el producto {$ml_id} desde Mercadolibre");
    
        // Verificar y refrescar token
        if (!$this->check_and_refresh_token()) {
            $this->log_error("No se pudo obtener un token de acceso válido.");
            return false; // Devolver false en caso de error de token
        }
    
        // Obtener datos del producto
        $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_id/?include_attributes=all";
        $response = $this->make_api_request_v2($endpoint, 'GET');
    
        if (is_wp_error($response)) {
            $this->log_error("Error al obtener información del producto de MercadoLibre: " . $response->get_error_message());
            return false;
        }
    
        $productoMercadolibre = json_decode(wp_remote_retrieve_body($response), true);
    
        // --- INICIO DE LA CORRECCIÓN ---
        // Se elimina la validación estricta que requería un SKU previo.
        // La función create_or_update_wc_product se encargará de asignar el ID de ML como SKU si es necesario.
        $sku = $productoMercadolibre['seller_custom_field'] ?? $ml_id;
        $this->log_debug("SKU para la operación: {$sku}");
        // --- FIN DE LA CORRECCIÓN ---
    
        // Obtener descripción
        $desc_endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_id/description";
        $desc_response = $this->make_api_request_sinLog($desc_endpoint, 'GET');
        if (!is_wp_error($desc_response)) {
            $description = json_decode(wp_remote_retrieve_body($desc_response), true);
            if (!empty($description)) {
                $productoMercadolibre['description'] = $description;
            }
        }
    
        // Obtener precios
        $productoMercadolibre['base_price'] = $productoMercadolibre['base_price'] ?? $productoMercadolibre['price'];
    
        // Crear o actualizar en WooCommerce
        if (!empty($productoMercadolibre)) {
            $res = $this->create_or_update_wc_product($productoMercadolibre);
    
            if (is_wp_error($res)) {
                $this->log_error("Error al crear o actualizar producto: " . $res->get_error_message());
                return false;
            }
    
            // Retornar el ID del producto creado/actualizado
            return ['wc_product_id' => $res];
        }
    
        $this->log_error("No se pudieron obtener datos válidos para el producto {$ml_id}.");
        return false;
    }



    /**
     * Realiza una solicitud a la API de MercadoLibre con manejo de errores y registro en caso de fallos.ඞ

     *
     * @param string $endpoint La URL del endpoint de la API de MercadoLibre.
     * @param string $method   El método HTTP para la solicitud (por defecto, 'GET'). Ej: 'GET', 'POST', 'PUT', 'DELETE'.
     * @param array|null $body Los datos del cuerpo de la solicitud (opcional). Se codifican como JSON si se proporciona.
     *
     * @return array|WP_Error La respuesta de la API en caso de éxito o un objeto WP_Error en caso de fallo.
     *
     * @throws Exception Si se producen errores inesperados en el proceso de solicitud.ඞ

     */
    private function make_api_request_v2($endpoint, $method = 'GET', $body = null)
    {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }
        $response = wp_remote_request($endpoint, $args);
        if (is_wp_error($response)) {
            $this->log_error('Error en la solicitud API a MercadoLibre: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }
    
    
        
    public function create_or_update_wc_product($ml_product_data, &$resumen = null)
    {
        // Desactivar hooks de sincronización de stock para evitar conflictos durante la importación.
        remove_action('woocommerce_product_set_stock', array($this, 'sync_stock_to_mercadolibre'), 10);
        remove_action('woocommerce_variation_set_stock', array($this, 'sync_variation_stock_to_mercadolibre'), 10);

        @set_time_limit(300);
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '512M');

        $producto_id_ml = $ml_product_data['id'] ?? 'ID desconocido';
        $resultado = [ /* ... */ ];

        try {
            $this->log_debug("🟡 ====== INICIANDO IMPORTACIÓN/ACTUALIZACIÓN PARA ML ID: {$producto_id_ml} ======");

            $is_variable = !empty($ml_product_data['variations']);
            $this->log_debug("Tipo de producto detectado: " . ($is_variable ? "Variable" : "Simple"));

            $id_wc_product = wc_get_product_id_by_sku($producto_id_ml);
            $wc_product = null;

            if ($id_wc_product) {
                $this->log_debug("Producto encontrado en WooCommerce con ID: {$id_wc_product}. Actualizando...");
                $wc_product = wc_get_product($id_wc_product);
            } else {
                $this->log_debug("Producto no encontrado en WooCommerce. Creando uno nuevo...");
                $wc_product = $is_variable ? new WC_Product_Variable() : new WC_Product_Simple();
            }

            $wc_product->set_sku($producto_id_ml);
            $wc_product->set_name(sanitize_text_field($ml_product_data['title'] ?? 'Producto de MercadoLibre'));
            $wc_product->set_description(wp_kses_post($ml_product_data['description']['plain_text'] ?? ''));
            
            if (!$is_variable) {
                $wc_product->set_regular_price($ml_product_data['price'] ?? 0);
                $wc_product->set_price($ml_product_data['price'] ?? 0);
                $wc_product->set_manage_stock(true);
                $wc_product->set_stock_quantity($ml_product_data['available_quantity'] ?? 0);
            } else {
                $wc_product->set_manage_stock(false);
            }

            if (!empty($ml_product_data['category_id'])) {
                $cat_endpoint = WOO_ML_API_ENDPOINT . "/categories/" . $ml_product_data['category_id'];
                $cat_response = $this->make_api_request_sinLog($cat_endpoint, 'GET');
                if (!is_wp_error($cat_response)) {
                    $cat_data = json_decode(wp_remote_retrieve_body($cat_response), true);
                    if (!empty($cat_data)) {
                        $this->create_or_update_categories_product($wc_product, $cat_data, ['save' => false]);
                    }
                }
            }
            $this->importar_imagenes_mercadolibre($producto_id_ml, $wc_product);

            $parent_product_id = $wc_product->save();
            $this->log_debug("Producto principal guardado con ID de WC: {$parent_product_id}");

            if ($is_variable) {
                $atributos_para_variacion = [];
                $posibles_atributos = [];
                foreach ($ml_product_data['variations'] as $v) {
                    foreach ($v['attribute_combinations'] as $comb) {
                        $attr_name = sanitize_title($comb['name']);
                        $taxonomy = 'pa_' . $attr_name;
                        $posibles_atributos[$taxonomy][] = $comb['value_name'];
                    }
                }

                foreach ($posibles_atributos as $tax => $values) {
                    $attribute = new WC_Product_Attribute();
                    $attribute->set_name($tax);
                    $attribute->set_options(array_unique($values));
                    $attribute->set_position(0);
                    $attribute->set_visible(true);
                    $attribute->set_variation(true);
                    $atributos_para_variacion[] = $attribute;
                }
                $wc_product->set_attributes($atributos_para_variacion);
                $wc_product->save();
                
                foreach ($ml_product_data['variations'] as $v) {
                    $sku_variacion = null;
                    if (!empty($v['attributes'])) {
                        foreach ($v['attributes'] as $attr) {
                            if ($attr['id'] === 'SELLER_SKU' && !empty($attr['value_name'])) {
                                $sku_variacion = $attr['value_name'];
                                break;
                            }
                        }
                    }

                    if (!$sku_variacion) {
                        $variation_ml_id = $v['id'];
                        $sku_variacion = "{$producto_id_ml}-{$variation_ml_id}";
                        $this->log_debug("⚠️ La variación con ID {$variation_ml_id} no tiene SELLER_SKU. Se usará un SKU de respaldo: {$sku_variacion}");
                    }

                    $variation_id = wc_get_product_id_by_sku($sku_variacion);
                    $variation = null;

                    if ($variation_id) {
                        // Se encontró un post con este SKU. ¿Es realmente una variación?
                        $post_type = get_post_type($variation_id);
                        if ($post_type === 'product_variation') {
                            $variation = new WC_Product_Variation($variation_id);
                        } else {
                            $this->log_debug("⚠️ Conflicto de SKU: El SKU '{$sku_variacion}' pertenece a un producto principal (ID: {$variation_id}). Se creará una nueva variación para evitar el error.");
                            $variation = new WC_Product_Variation();
                        }
                    } else {
                        // No se encontró producto con este SKU, se crea uno nuevo.
                        $variation = new WC_Product_Variation();
                    }
                    // --- FIN DE LA CORRECCIÓN ---

                    $variation->set_parent_id($parent_product_id);
                    $variation->set_sku($sku_variacion);
                    
                    $this->log_debug("Procesando variación. SKU asignado: {$sku_variacion}");

                    $variation->set_regular_price($v['price'] ?? 0);
                    $variation->set_price($v['price'] ?? 0);
                    $variation->set_manage_stock(true);
                    $variation->set_stock_quantity($v['available_quantity'] ?? 0);

                    $atributos_de_combinacion = [];
                    foreach ($v['attribute_combinations'] as $comb) {
                        $taxonomy = 'pa_' . sanitize_title($comb['name']);
                        $term_slug = sanitize_title($comb['value_name']);
                        $atributos_de_combinacion[$taxonomy] = $term_slug;
                    }
                    $variation->set_attributes($atributos_de_combinacion);
                    
                    $variation->save();
                }
            }

            update_post_meta($parent_product_id, '_mercadolibre_id', $producto_id_ml);
            update_post_meta($parent_product_id, '_datos_mercadolibre', $ml_product_data);

            $resultado['success'] = true;
            $resultado['woo_id'] = $parent_product_id;
            $this->log_debug("🟢 [ÉXITO] Producto ML {$producto_id_ml} procesado. ID de WooCommerce: {$parent_product_id}");

        } catch (Exception $e) {
            $resultado['error'] = $e->getMessage();
            $this->log_error("🔴 [ERROR CRÍTICO] Procesando {$producto_id_ml}: " . $e->getMessage());
        }

        // --- INICIO DE LA CORRECCIÓN ---
        // Reactivar los hooks de sincronización de stock.
        add_action('woocommerce_product_set_stock', array($this, 'sync_stock_to_mercadolibre'), 10, 1);
        add_action('woocommerce_variation_set_stock', array($this, 'sync_variation_stock_to_mercadolibre'), 10, 2);
        // --- FIN DE LA CORRECCIÓN ---

        return $resultado['success'] ? $resultado['woo_id'] : new WP_Error(
            'product_update_failed',
            $resultado['error'] ?: 'Error desconocido al crear/actualizar producto.'
        );
    }


private function get_stock_virtual_ml($ml_product_id) {
    try {
        // Obtener información completa del item para conseguir el seller_sku
        $endpoint_item = WOO_ML_API_ENDPOINT . "/items/{$ml_product_id}/stock";
        $response_item = $this->make_api_request_v2($endpoint_item, 'GET');

        if (is_wp_error($response_item)) {
            $this->log_debug("🔴 Error al obtener item: " . $response_item->get_error_message());
            return null;
        }

        $item_data = json_decode(wp_remote_retrieve_body($response_item), true);
        $seller_sku = $item_data['seller_custom_field'] ?? null;

        if (!$seller_sku) {
            $this->log_debug("🔴 No se encontró seller_sku en el item.");
            return null;
        }

        // Llamar al endpoint de inventario
        $endpoint = WOO_ML_API_ENDPOINT . "/inventory_items/{$seller_sku}"; 
        $response = $this->make_api_request_v2($endpoint, 'GET');

        if (is_wp_error($response)) {
            $this->log_debug("🔴 Error al obtener inventario: " . $response->get_error_message());
            return null;
        }

        $stock_data = json_decode(wp_remote_retrieve_body($response), true);
        
        $this->log_debug("📦 [STOCK_DATA] SKU: {$seller_sku} - " . print_r($stock_data, true));

        if (!empty($stock_data['available_quantity'])) {
            return [
                'available_quantity' => $stock_data['available_quantity'] ?? 0,
                'reserved_quantity' => $stock_data['reserved_quantity'] ?? 0
                // Aquí no se devuelve 'virtual_available_quantity' porque no existe como tal
            ];
        }

        return null;

    } catch (Exception $e) {
        $this->log_debug("🔴 Excepción al obtener stock virtual: " . $e->getMessage());
        return null;
    }
}


/**
 * Método para actualizar el stock considerando el stock virtual

private function update_stock_with_virtual($wc_product, $ml_product_data, $variation_data = null) {
    $ml_product_id = $ml_product_data['id'] ?? null;
    
    if (!$ml_product_id) {
        return;
    }
    
    // Para variaciones
    if ($variation_data && !empty($variation_data['id'])) {
        $stock_info = $this->get_stock_virtual_ml($ml_product_id, $variation_data['id']);
        
        if ($stock_info) {
            // Usar stock virtual si está disponible, sino usar available_quantity
            $stock_quantity = $stock_info['virtual_available_quantity'] > 0 
                ? $stock_info['virtual_available_quantity'] 
                : ($variation_data['available_quantity'] ?? 0);
            
            $wc_product->set_manage_stock(true);
            $wc_product->set_stock_quantity($stock_quantity);
            
            // Guardar información adicional del stock como meta
            if (method_exists($wc_product, 'get_id') && $wc_product->get_id()) {
                update_post_meta($wc_product->get_id(), '_ml_stock_info', $stock_info);
                update_post_meta($wc_product->get_id(), '_ml_virtual_stock', $stock_info['virtual_available_quantity']);
                update_post_meta($wc_product->get_id(), '_ml_sold_quantity', $stock_info['sold_quantity']);
                update_post_meta($wc_product->get_id(), '_ml_reserved_quantity', $stock_info['reserved_quantity']);
            }
            
            $this->log_debug("🟡 Stock virtual actualizado para variación: {$stock_quantity}");
        } else {
            // Fallback al método original
            $stock = $variation_data['available_quantity'] ?? 0;
            $wc_product->set_manage_stock(true);
            $wc_product->set_stock_quantity($stock);
        }
    } 
    // Para productos simples
    else {
        $stock_info = $this->get_stock_virtual_ml($ml_product_id);
        
        if ($stock_info) {
            // Usar stock virtual si está disponible, sino usar available_quantity
            $stock_quantity = $stock_info['virtual_available_quantity'] > 0 
                ? $stock_info['virtual_available_quantity'] 
                : ($ml_product_data['available_quantity'] ?? 0);
            
            $wc_product->set_manage_stock(true);
            $wc_product->set_stock_quantity($stock_quantity);
            
            // Guardar información adicional del stock como meta
            if (method_exists($wc_product, 'get_id') && $wc_product->get_id()) {
                update_post_meta($wc_product->get_id(), '_ml_stock_info', $stock_info);
                update_post_meta($wc_product->get_id(), '_ml_virtual_stock', $stock_info['virtual_available_quantity']);
                update_post_meta($wc_product->get_id(), '_ml_sold_quantity', $stock_info['sold_quantity']);
                update_post_meta($wc_product->get_id(), '_ml_reserved_quantity', $stock_info['reserved_quantity']);
            }
            
            $this->log_debug("🟡 Stock virtual actualizado para producto simple: {$stock_quantity}");
        } else {
            // Fallback al método original
            $stock = $ml_product_data['available_quantity'] ?? 0;
            $wc_product->set_manage_stock(true);
            $wc_product->set_stock_quantity($stock);
        }
    }
}
 */
/**
 * Método para verificar si el stock virtual está habilitado
 */
// private function is_virtual_stock_enabled() {
    
//     // Puedes crear una opción en tu panel de administración
//     return true;
// }


// public function get_attachment_id_from_ml_id($ml_picture_id) {
//     $query_args = array(
//         'post_type'  => 'attachment',
//         'meta_key'   => '_ml_picture_id',
//         'meta_value' => $ml_picture_id,
//         'posts_per_page' => 1,
//         'fields'     => 'ids',
//     );
//     $query = new WP_Query($query_args);
//     if (!empty($query->posts)) {
//         return $query->posts[0];
//     }
//     return false;
// }

    /**
     * Crea o actualiza la clase de envío y asigna los datos de envío al producto de WooCommerce.ඞ

     *
     * Este método comprueba si la clase de envío "Mercado Envíos" ya existe. Si no existe, la crea.ඞඞඞඞඞ
     * Luego, asigna la clase de envío al producto de WooCommerce y actualiza el peso y dimensiones del producto,ඞඞඞ
     * basándose en los datos de envío proporcionados por MercadoLibre.
     *
     * @param WC_Product $wc_product El objeto del producto de WooCommercඞඞe que se va a actualizar.ඞ
     * @param array $ml_product_data Los datos del producto de MercadoLibre, incluyendo los datos de envío.
     * @param array $options Opciones adicionales para el método:
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.ඞ

     */
    public function create_or_update_garantia($wc_product, $ml_product_data, $options = ['save' => false])
    {
        try {
            // Verificar si existen datos de garantía en Mercadolibre
            if (empty($ml_product_data['sale_terms'])) {
                $this->log_debug("No se encontraron datos de garantía en el producto de MercadoLibre.");
                return false;
            }

            $atributosWC = $wc_product->get_attributes();
            $atributosGarantia = [];

            // Crear un mapa de los atributos actuales para búsqueda rápida
            $atributosWCMap = [];
            foreach ($atributosWC as $atributo) {
                $atributosWCMap[$atributo['term_name']] = $atributo;
            }

            // Por cada dato de garantía en Mercadolibre
            foreach ($ml_product_data['sale_terms'] as $sale_term) {
                $nombreAtributo = $sale_term['name'];
                $valorAtributo = $sale_term['value_name'];
                $idAtributo = $sale_term['id'];

                // Si el atributo ya existe, actualízalo
                if (isset($atributosWCMap[$nombreAtributo])) {
                    $atributoExistente = $atributosWCMap[$nombreAtributo];
                    $atributoExistente->set_options([$valorAtributo]);
                    $atributosGarantia[] = $atributoExistente;
                } else {
                    // Si el atributo no existe, créalo
                    $nuevoAtributo = new WC_Product_Attribute();
                    $nuevoAtributo->set_id($idAtributo);
                    $nuevoAtributo->set_name($nombreAtributo);
                    $nuevoAtributo->set_options([$valorAtributo]);
                    $nuevoAtributo->set_visible(true);
                    $atributosGarantia[] = $nuevoAtributo;
                }
            }

            // Combinar los atributos de garantía con los atributos actuales
            $atributosFinales = [];
            foreach ($atributosGarantia as $atributo) {
                $atributosFinales[] = $atributo;
            }
            foreach ($atributosWC as $atributo) {
                $atributosFinales[] = $atributo;
            }

            // Actualizar los atributos del producto
            $wc_product->set_attributes($atributosFinales);

            // Guardar cambios si la opción 'save' está habilitada
            if (!empty($options['save'])) {
                $wc_product->save();
            }

            return true; // Éxito
        } catch (Exception $e) {
            // Log de error
            $this->log_debug("Error al crear o actualizar los datos de garantía: " . $e->getMessage());

            return new WP_Error(
                'shipping_update_failed',
                'Ocurrió un error al crear o actualizar los datos de garantía: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea o actualiza los atributos obligatorios de un producto en WooCommerce basados en los datos de MercadoLibre.
     *
     * Este método recorre los atributos obligatorios definidos, verifica si ya existen en el producto de WooCommerce,
     * y si es necesario, los crea o actualiza. Los valores de los atributos se obtienen de los datos de MercadoLibre.
     *
     * @param WC_Product $wc_product El objeto del producto de WooCommerce que se va a actualizar.
     * @param array $ml_data Los datos del producto de MercadoLibre.
     * @param array $options Opciones adicionales para la operación (opcional).
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.
     */
    // public function create_or_update_must_attributes_product($wc_product, $ml_data, $options = ['save' => false])
    // {
    //     try {
    //         // Atributos obligatorios
    //         $must_attributes = [
    //             ['id' => 'canal_de_ventas', 'name' => 'Canal de Ventas',     'default_value' => 'Mercadolibre',],
    //             ['id' => 'currency_id',     'name' => 'Moneda de pago',      'default_value' => 'CLP',],
    //             ['id' => 'buying_mode',     'name' => 'Modo de compra',      'default_value' => 'buy_it_now',],
    //             ['id' => 'listing_type_id', 'name' => 'Tipo de publicación', 'default_value' => 'gold_special',]
    //         ];

    //         // Validamos que se recibieron los datos necesarios
    //         if (empty($must_attributes)) {
    //             return new WP_Error(
    //                 'invalid_attributes',
    //                 'Los atributos obligatorios no están disponibles o no son válidos.'
    //             );
    //         }

    //         // Obtenemos los atributos existentes del producto en WooCommerce
    //         $existing_attributes = $wc_product->get_attributes(); // Array de atributos existentes del producto
    //         $must_attributes_ids = [];

    //         // Recorremos los atributos obligatorios
    //         foreach ($must_attributes as $must_data) {
    //             // Creamos un nuevo objeto de atributo para WooCommerce
    //             $attribute = new WC_Product_Attribute();
    //             $attribute->set_id($must_data['id']);
    //             $attribute->set_name($must_data['name']);

    //             // Verificar si existe el atributo en el producto actual
    //             $existing_attribute = null;
    //             foreach ($existing_attributes as $existing_attr) {
    //                 if ($existing_attr->get_name() == $must_data['name']) {
    //                     $existing_attribute = $existing_attr;
    //                     break;
    //                 }
    //             }

    //             // Si el atributo existe, actualizamos sus valores
    //             if ($existing_attribute) {
    //                 if (!empty($ml_data[$must_data['id']])) {
    //                     $existing_attribute->set_options([$ml_data[$must_data['id']]]);
    //                 } else {
    //                     // Si no hay datos, usamos el valor por defecto
    //                     $existing_attribute->set_options([$must_data['default_value']]);
    //                 }
    //             } else {
    //                 // Si el atributo no existe, lo creamos con los valores correspondientes
    //                 if (!empty($ml_data[$must_data['id']])) {
    //                     $attribute->set_options([$ml_data[$must_data['id']]]);
    //                 } else {
    //                     $attribute->set_options([$must_data['default_value']]);
    //                 }
    //             }

    //             // Establecemos el atributo como no visible
    //             $attribute->set_visible(false);
    //             // Agregamos el atributo a la lista
    //             $must_attributes_ids[] = $attribute;
    //         }

    //         // Asignamos los atributos al producto de WooCommerce
    //         $wc_product->set_attributes($must_attributes_ids);

    //         // Si la opción 'save' está activada, guardamos el producto
    //         if (!empty($options['save'])) {
    //             $wc_product->save();
    //         }
    //         return true; // Retornamos true indicando que todo salió bien

    //     } catch (Exception $e) {
    //         // Log de error
    //         $this->log_debug("Error al crear o actualizar los atributos obligatorios: " . $e->getMessage());

    //         return new WP_Error(
    //             'attributes_update_failed',
    //             'Ocurrió un error al crear o actualizar los atributos obligatorios: ' . $e->getMessage()
    //         );
    //     }
    // }

    /**
     * Crea o actualiza las variaciones de un producto en WooCommerce basadas en las variaciones de MercadoLibre.
     *
     * @param WC_Product $wc_product El objeto de producto de WooCommerce que se va a actualizar.
     * @param array $ml_data Los datos de variaciones de MercadoLibre ya procesados.
     * @param array $options Opciones adicionales para el método:
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.
     */
// public function create_or_update_variations_product($wc_product, $ml_variaciones, $ml_product_data)
// {
//     $this->log_debug("Comenzando importacion de variaciones");
//     $existing_variations = $wc_product->get_children();

//     foreach ($ml_variaciones as $v) {
//         try {
//             $sku = array_column($v['attributes'], 'value_name', 'id')['SELLER_SKU'] ?? null;
//             if (!$sku) {
//                 $this->log_debug("❌ Variación sin SKU. Saltando.");
//                 continue;
//             }

//             // Buscar si ya existe una variación con este SKU
//             $variation_id = wc_get_product_id_by_sku($sku);
//             $is_new = false;

//             if ($variation_id) {
//                 $variation = new WC_Product_Variation($variation_id);
//                 $this->log_debug("🔁 Actualizando variación existente SKU: {$sku}");
//             } else {
//                 $variation = new WC_Product_Variation();
//                 $variation->set_parent_id($wc_product->get_id());
//                 $is_new = true;
//                 $this->log_debug("🆕 Creando nueva variación SKU: {$sku}");
//             }

//             // Asignar datos de variación
//             $variation->set_sku($sku);

//             // Asignar precio si viene
//             $precio = $v['price'] ?? null;
//             if ($precio && is_numeric($precio) && $precio > 0) {
//                 $variation->set_regular_price($precio);
//                 $variation->set_sale_price($precio);
//                 $variation->set_price($precio);
//                 $this->log_debug("💰 Precio asignado a variación SKU {$sku}: {$precio}");
//             } else {
//                 $this->log_debug("⚠️ Precio no válido en SKU {$sku}");
//             }

//             // Asignar stock
//             $stock = $v['available_quantity'] ?? null;
//             if (is_numeric($stock)) {
//                 $variation->set_manage_stock(true);
//                 $variation->set_stock_quantity($stock);
//                 $this->log_debug("📦 Stock asignado a variación SKU {$sku}: {$stock}");
//             } else {
//                 $variation->set_manage_stock(false);
//                 $this->log_debug("⚠️ Stock no asignado en SKU {$sku}");
//             }

//             // Asignar atributos a la variación
//             $variation_attributes = [];
//             foreach ($v['attribute_combinations'] as $comb) {
//                 $taxonomy = 'pa_' . sanitize_title($comb['name']);
//                 $term_slug = sanitize_title($comb['value_name']);
//                 $variation_attributes[$taxonomy] = $term_slug;
//             }

//             $variation->set_attributes($variation_attributes);
//             $variation->save();
//             wc_delete_product_transients($variation->get_id());
//             wp_cache_flush();

//             // Asociar variación al producto si es nueva
//             if ($is_new) {
//                 wc_delete_product_transients($wc_product->get_id());
//             }
//         } catch (Exception $e) {
//             $this->log_debug("⛔ Error procesando variación: " . $e->getMessage());
//             continue;
//         }
//     }

//     $this->log_debug("✅ Variaciones actualizadas.");
// }
    /**
     * Crea o actualiza las categorías de un producto en WooCommerce basadas en datos de MercadoLibre.
     *
     * Este método toma las categorías procesadas desde MercadoLibre, las mapea a categorías de WooCommerce
     * y las asigna al producto dado. Opcionalmente, guarda el producto.
     *
     * @param WC_Product $wc_product El objeto de producto de WooCommerce que se va a actualizar.
     * @param array $ml_data Los datos de categorías de MercadoLibre ya procesados (path_from_root).
     * @param array $options Opciones adicionales para el método:
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.
     */
    public function create_or_update_categories_product($wc_product, $ml_data, $options = ['save' => false])
    {
        try {
            // Validar que se recibió un array de categorías
            if (!is_array($ml_data) || empty($ml_data['path_from_root'])) {
                return new WP_Error(
                    'invalid_categories_data',
                    'Los datos de categorías de MercadoLibre no son válidos o están vacíos.'
                );
            }

            $wc_categories = [];

            // Procesar las categorías desde la raíz
            foreach ($ml_data['path_from_root'] as $cat) {
                // Verificar si la categoría ya existe en WooCommerce
                $term = term_exists($cat['name'], 'product_cat');
                $slug = sanitize_title($cat['name']);

                if ($term == 0 || $term == null) {
                    // Si no existe, crear la categoría
                    $category_wc = wp_insert_term(
                        $cat['name'], // Nombre de la categoría
                        'product_cat', // Taxonomía
                        ['slug' => $slug]
                    );

                    if (is_wp_error($category_wc)) {
                        $this->log_debug("Error al crear la categoría " . $cat['name'] . ": " . $category_wc->get_error_message());
                        return new WP_Error(
                            'category_creation_failed',
                            'No se pudo crear la categoría: ' . $cat['name']
                        );
                    }

                    $wc_categories[] = $category_wc['term_id'];
                    $this->log_debug("Se creó la categoría " . $cat['name'] . " con ID: " . $category_wc['term_id']);
                } else {
                    $wc_categories[] = $term['term_id'];
                }
            }

            // Asignar las categorías al producto de WooCommerce
            $wc_product->set_category_ids($wc_categories);

            // Guardar el producto si la opción 'save' está activada
            if (!empty($options['save'])) {
                $wc_product->save();
                $this->log_debug("El producto de WooCommerce ha sido guardado con las categorías asignadas.");
            }
            return true;
        } catch (Exception $e) {
            // Log de error
            $this->log_debug("Error al procesar las categorías: " . $e->getMessage());

            return new WP_Error(
                'categories_update_failed',
                'Ocurrió un error al crear o actualizar las categorías: ' . $e->getMessage()
            );
        }
    }

    /**
     * Crea o actualiza los atributos de un producto en WooCommerce basados en los datos de MercadoLibre.
     *
     * Este método extrae los atributos del producto de MercadoLibre, los mapea a los atributos de WooCommerce
     * y los asigna al producto dado. Opcionalmente, guarda el producto después de actualizar los atributos.
     *
     * @param WC_Product $wc_product El objeto de producto de WooCommerce que se va a actualizar.
     * @param array $ml_data Los datos del producto de MercadoLibre, incluyendo los atributos.
     * @param array $options Opciones adicionales para el método:
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, true.
     *                        - 'variation' (bool): Indica si el atributo es usado para variaciones. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.
     */
//     public function create_or_update_attributes_product($wc_product, $ml_data, $options = ['save' => true, 'variation' => false])
// {
//     $ml_attributes = $ml_data['attributes'];
//     $attributes = $wc_product->get_attributes();
//     $this->log_debug("*** Atributos globales ***");

//     foreach ($ml_attributes as $atributo) {
//         $nombre_atributo = $atributo['name'];
//         $valor_atributo = sanitize_text_field($atributo['value_name']);

//         if (empty($nombre_atributo) || empty($valor_atributo)) {
//             continue;
//         }

//         // Buscar atributo existente
//         $attribute_id = wc_attribute_taxonomy_id_by_name($nombre_atributo);

//         if (!$attribute_id) {
//             $slug = substr(wc_sanitize_taxonomy_name($nombre_atributo), 0, 25);
//             $attribute_id = wc_attribute_taxonomy_id_by_name($slug);
//         }

//         // Crear si no existe
//         if (!$attribute_id) {
//             $this->log_debug("🆕 Creando atributo global: " . $nombre_atributo);
//             $slug = substr(wc_sanitize_taxonomy_name($nombre_atributo), 0, 25);

//             $attribute_id = wc_create_attribute([
//                 'name' => $nombre_atributo,
//                 'slug' => $slug,
//                 'type' => 'select'
//             ]);

//             if (is_wp_error($attribute_id)) {
//                 $this->log_debug("❌ Error al crear atributo global: " . $attribute_id->get_error_message());
//                 continue;
//             }
//         }

//         $taxonomy = wc_attribute_taxonomy_name_by_id($attribute_id);
//         $term_id = null;

//         // Insertar término si no existe
//         $term = get_term_by('name', $valor_atributo, $taxonomy);
//         if (!$term) {
//             $term_result = wp_insert_term($valor_atributo, $taxonomy);
//             if (!is_wp_error($term_result)) {
//                 $term_id = $term_result['term_id'];
//                 $this->log_debug("🆕 Término insertado '{$valor_atributo}' en {$taxonomy}");
//             } else {
//                 $this->log_error("❌ Error al insertar término: " . $term_result->get_error_message());
//                 continue;
//             }
//         } else {
//             $term_id = $term->term_id;
//         }

//         // Asociar el término al producto
//         wp_set_object_terms($wc_product->get_id(), $valor_atributo, $taxonomy, true);

//         // Preparar atributo del producto
//         $existing_terms = isset($attributes[$taxonomy]) ? $attributes[$taxonomy]->get_options() : [];
//         $new_terms = array_unique(array_merge($existing_terms, [$term_id]));

//         $product_attribute = new WC_Product_Attribute();
//         $product_attribute->set_id($attribute_id);
//         $product_attribute->set_name($taxonomy);
//         $product_attribute->set_options($new_terms);
//         $product_attribute->set_visible(true);
//         $product_attribute->set_variation($options['variation']); // <- usado para variaciones si corresponde

//         $attributes[$taxonomy] = $product_attribute;
//     }

//     $wc_product->set_attributes($attributes);

//     if ($options['save']) {
//         $wc_product->save();
//         $this->log_debug("✅ Atributos globales actualizados y guardados");
//     } else {
//         $this->log_debug("✅ Atributos globales actualizados (no guardado inmediato)");
//     }

//     return true;
// }


    //Por cada producto existente en WooCommerce, crea un producto en Mercadolibre
    public function importarTodoMercadolibre()
    {
    // Verifica nonce para evitar ataques CSRF
    check_ajax_referer('importarTodoMercadolibre_nonce', 'nonce');

    // Verifica token de Acceso
    if (!$this->check_and_refresh_token()) {
        wp_send_json_error(__('Error: Token inválido o caducado.', 'woo-ml-sync'));
    }

    $this->log_debug("Iniciando la obtención de información de productos de Mercadolibre (solo visualización).");

    // Recupera parámetros enviados desde el front-end
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $scroll_id = isset($_POST['scroll_id']) ? sanitize_text_field($_POST['scroll_id']) : null;

    try {
        // Obtener el usuario de MercadoLibre
        $user_endpoint = WOO_ML_API_ENDPOINT . '/users/me';
        $user_response = $this->make_api_request_v2($user_endpoint, 'GET');

        if (is_wp_error($user_response)) {
            throw new Exception('Error al obtener el usuario de Mercadolibre: ' . $user_response->get_error_message());
        }

        $user_body = json_decode(wp_remote_retrieve_body($user_response), true);
        $user_id = $user_body['id'];

        // Obtener productos
        $products_endpoint = $scroll_id
            ? WOO_ML_API_ENDPOINT . "/users/{$user_id}/items/search?limit=100&search_type=scan&scroll_id={$scroll_id}"
            : WOO_ML_API_ENDPOINT . "/users/{$user_id}/items/search?limit=100&search_type=scan";

        $products_response = $this->make_api_request_v2($products_endpoint, 'GET');

        if (is_wp_error($products_response)) {
            throw new Exception(__('Error al obtener productos de Mercadolibre: ', 'woo-ml-sync') . $products_response->get_error_message());
        }

        $products_body = json_decode(wp_remote_retrieve_body($products_response), true);
        $new_scroll_id = $products_body['scroll_id'] ?? null;

        // Procesar cada producto
        if (!empty($products_body['results'])) {
            foreach ($products_body['results'] as $ml_product_id) {
                $product_detail_endpoint = WOO_ML_API_ENDPOINT . "/items/{$ml_product_id}";
                $product_detail_response = $this->make_api_request_v2($product_detail_endpoint, 'GET');

                if (!is_wp_error($product_detail_response)) {
                    $product_detail = json_decode(wp_remote_retrieve_body($product_detail_response), true);

                    // Separador entre productos
                    $this->log_debug("========================================");

                    // Datos básicos del producto
                    $this->log_debug("Nombre: " . ($product_detail['title'] ?? 'Sin nombre'));
                    $this->log_debug("Estado: " . (isset($product_detail['status']) ? ($product_detail['status'] === 'active' ? 'Activo' : 'Inactivo') : 'Estado desconocido'));
                    $this->log_debug("N° Publicación: " . $ml_product_id);

                    // Obtener SKU correcto (de los atributos)
                    $sku = 'Sin SKU';
                    if (isset($product_detail['attributes']) && is_array($product_detail['attributes'])) {
                        foreach ($product_detail['attributes'] as $attribute) {
                            if ($attribute['id'] === 'SELLER_SKU') {
                                $sku = $attribute['value_name'] ?? 'Sin SKU';
                                break;
                            }
                        }
                    }
                    $this->log_debug("SKU: " . $sku);

                    // Obtener jerarquía completa de categorías
                    if (isset($product_detail['category_id'])) {
                        $category_endpoint = WOO_ML_API_ENDPOINT . "/categories/{$product_detail['category_id']}";
                        $category_response = $this->make_api_request_v2($category_endpoint, 'GET');

                        if (!is_wp_error($category_response)) {
                            $category_data = json_decode(wp_remote_retrieve_body($category_response), true);

                            // Mostrar toda la jerarquía de categorías
                            if (!empty($category_data['path_from_root'])) {
                                $jerarquia_categorias = array_map(function($cat) {
                                    return $cat['name'];
                                }, $category_data['path_from_root']);

                                $this->log_debug("Categoría completa: " . implode(' > ', $jerarquia_categorias));
                            } else {
                                $this->log_debug("Categoría: " . ($category_data['name'] ?? 'Sin categoría'));
                            }

                            $this->log_debug("Código Categoría: " . $product_detail['category_id']);
                        }
                    } else {
                        $this->log_debug("Categoría: Sin categoría");
                        $this->log_debug("Código Categoría: Sin código");
                    }
                }
            }
            // Separador final
            $this->log_debug("========================================");
        }

        // Determinar si hay más productos
        $has_more = !empty($products_body['results']) || !empty($new_scroll_id);

        // Respuesta final
        wp_send_json_success([
            'scroll_id' => $new_scroll_id,
            'offset' => $offset + 100,
            'has_more' => $has_more,
            'message' => 'Obteniendo información de productos...'
        ]);

    } catch (Exception $e) {
        $this->log_debug("========================================");
        $this->log_debug("Error al obtener información: " . $e->getMessage());
        $this->log_debug("========================================");
        wp_send_json_error($e->getMessage());
    }
}

    public function sync_all_products()
    {
        //En la primera tanda, obtener todos los productos de WooCommerce y almacenar sus IDs en un array
        if ($_POST['pendientes'] == "[]") {
            $args = array(
                'limit' => -1, // Obtener todos los productos
                'status' => 'publish', // Solo productos publicados
                'category' => array('pijamas'), //Categoria pijamas
                'return' => 'objects', // Retorna una lista de objetos WC_Product
            );
            $productos_wc =   wc_get_products($args);
            $pendientes = [];
            foreach ($productos_wc as $producto) {
                $pendientes[] = $producto->get_id();
            }
        } else {
            $pendientes = json_decode(stripslashes($_POST['pendientes']), true);
        }

        $importados = isset($_POST['importados']) ? intval($_POST['importados']) : 0;
        $total = count($pendientes) + $importados;
        $this->log_debug("Se han importado ." . $importados . " productos de un total de " . $total);

        if (!empty($pendientes)) {
            $id_producto = array_shift($pendientes); //Sacar el primer producto del array
            $producto = wc_get_product($id_producto);
            $this->sync_product_to_mercadolibre($producto); //FALTA MANEJAR LOS ERRORES
            $importados++;
        } else {
            $this->log_debug("Se han importado correctamente todos los productos de WooCommerce a Mercadolibre");
            exit();
        }

        // Responder con el estado actual
        wp_send_json_success([
            'importados' => $importados,
            'total' => $total, // Contar correctamente los productos restantes
            'pendientes' => $pendientes, // Retornar los productos restantes
        ]);
    }

    //Borra los tokens de opciones y de atributos
    private function logout()
    {
        if (!isset($_POST['ml_logout_nonce']) || !wp_verify_nonce($_POST['ml_logout_nonce'], 'ml_logout')) {
            $this->log_error('Error de seguridad al cerrar sesión.');
            return;
        }
        delete_option('woo_ml_access_token');
        delete_option('woo_ml_refresh_token');
        delete_option('woo_ml_token_expiration');
        $this->access_token = null;
        $this->refresh_token = null;
        add_settings_error('woo_ml_messages', 'logout_success', __('Se ha cerrado la sesión de MercadoLibre.', 'woo-ml-sync'), 'success');
    }

    //Testea la conexion con Mercadolibre Loggeando errores
    private function test_mercadolibre_connection()
    {
        if (!$this->check_and_refresh_token()) {
            $this->log_error('No se pudo obtener un token de acceso válido para probar la conexión.');
            add_settings_error('woo_ml_messages', 'connection_error', __('Error: No se pudo obtener un token de acceso válido.', 'woo-ml-sync'), 'error');
            return;
        }

        $endpoint = WOO_ML_API_ENDPOINT . '/users/me';
        $response = $this->make_api_request($endpoint, 'GET');

        if (is_wp_error($response)) {
            $this->log_error('Error al probar la conexión con MercadoLibre: ' . $response->get_error_message());
            add_settings_error('woo_ml_messages', 'connection_error', __('Error al probar la conexión con MercadoLibre:', 'woo-ml-sync') . ' ' . $response->get_error_message(), 'error');
            return;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 200 && isset($body['id'])) {
            $this->log_debug('Conexión exitosa con MercadoLibre. ID de usuario: ' . $body['id']);
            add_settings_error('woo_ml_messages', 'connection_success', __('Conexión exitosa con MercadoLibre. ID de usuario:', 'woo-ml-sync') . ' ' . $body['id'], 'success');
        } else {
            $error_message = isset($body['message']) ? $body['message'] : __('Error desconocido', 'woo-ml-sync');
            $this->log_error('Error al probar la conexión con MercadoLibre. Código: ' . $status_code . '. Mensaje: ' . $error_message);
            add_settings_error('woo_ml_messages', 'connection_error', __('Error al probar la conexión con MercadoLibre. Código:', 'woo-ml-sync') . ' ' . $status_code . '. ' . __('Mensaje:', 'woo-ml-sync') . ' ' . $error_message, 'error');
        }
    }

    //Método para simplificar las peticiones a la API
    private function make_api_request($endpoint, $method = 'GET', $body = null)
    {
        if (!$this->check_and_refresh_token()) {
            return new WP_Error('token_error', __('No se pudo obtener un token de acceso válido.', 'woo-ml-sync'));
        }

        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            $this->log_error('Error en la solicitud API a MercadoLibre: ' . $response->get_error_message());
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        $this->log_debug("Respuesta de MercadoLibre (Endpoint: $endpoint, Método: $method):");
        $this->log_debug("Código de estado: $status_code");
        $this->log_debug("Cuerpo de la respuesta: $response_body");

        return $response;
    }

    //Método para simplificar las peticiones a la API
    private function make_api_request_sinLog($endpoint, $method = 'GET', $body = null)
    {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->access_token,
                'Content-Type' => 'application/json',
            ),
            'timeout' => 30,
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }
        $response = wp_remote_request($endpoint, $args);
        if (is_wp_error($response)) {
            $this->log_error('Error en la solicitud API a MercadoLibre: ' . $response->get_error_message());
            return $response;
        }

        return $response;
    }
    private function log_debug($message)
    {
        $this->debug_messages[] = '[Debug] ' . $message;
        $this->write_log('debug', $message);
    }

    private function log_error($message)
    {
        $this->debug_messages[] = '[Error] ' . $message;
        $this->write_log('error', $message);
        error_log('WooCommerce MercadoLibre Sync Error: ' . $message);
    }

    private function write_log($type, $message)
    {
        $log_file = WP_CONTENT_DIR . '/woo-ml-sync.log';
        $timestamp = current_time('mysql');
        $log_message = "[$timestamp] [$type] $message\n";
        error_log($log_message, 3, $log_file);
    }

    // private function log_sku($id_ml)
    // {
    //     $log_file = WP_CONTENT_DIR . '/productos-sin-sku.log';
    //     $log_message = "\n" . "ID Mercadolibre: " . $id_ml;
    //     error_log($log_message, 3, $log_file);
    //     error_log("id es $id_ml");
    //     $log_file = WP_CONTENT_DIR . '/productos-sin-sku.log';
    // }

    // public function clear_sku_log()
    // {
    //     $log_file = WP_CONTENT_DIR . '/productos-sin-sku.log';
    //     file_put_contents($log_file, '');
    //     $message = "PRODUCTOS SIN SKU:/nFecha: " . date("Y-m-d H:i:s") . "/n";
    //     error_log($message, 3, $log_file);
    // }

    //Añade la Guia de Tallas (a donde?)
    // public function add_size_grid_id_field()
    // {
    //     global $woocommerce, $post;
    //     echo '<div class="options_group">';
    //     woocommerce_wp_text_input(
    //         array(
    //             'id' => 'SIZE_GRID_ID',
    //             'label' => __('SIZE GRID ID', 'woo-ml-sync'),
    //             'desc_tip' => 'true',
    //             'description' => __('Ingrese el ID de la grilla de tallas de MercadoLibre.', 'woo-ml-sync')
    //         )
    //     );
    //     echo '</div>';
    // }

    //Realiza una consulta a la API de mercadolibre para obtener el Status del producto
    private function obtener_producto_ml($ml_product_id)
    {
        $endpoint = WOO_ML_API_ENDPOINT . "/items/" . $ml_product_id . "?include_attributes=all";
        $response = $this->make_api_request_v2($endpoint, 'GET');

        if (is_wp_error($response)) {
            $this->log_error("Error al obtener el estado del producto de MercadoLibre: " . $response->get_error_message());
            return null;
        }

        $producto_ml = json_decode(wp_remote_retrieve_body($response), true);
        return isset($producto_ml) ? $producto_ml : null;
    }

    //Método que aparentemente no está en uso
    // private function validate_size_grid_id($size_grid_id)
    // {
    //     $endpoint = WOO_ML_API_ENDPOINT . "/catalog/charts/" . $size_grid_id;
    //     $response = $this->make_api_request_v2($endpoint, 'GET');

    //     if (is_wp_error($response)) {
    //         $this->log_error("Error al validar SIZE_GRID_ID: " . $response->get_error_message());
    //         return false;
    //     }

    //     $status_code = wp_remote_retrieve_response_code($response);
    //     return $status_code === 200;
    // }

    //Añade la data del Shipping
    private function add_shipping_mode(&$data)
    {
        $data['shipping'] = array(
            'mode' => 'me2',
            'local_pick_up' => true,
            'free_shipping' => false,
            'logistic_type' => 'not_specified',
            'methods' => array(
                array('id' => 'me2')
            )
        );
    }

    //Añade guia de tallas(A donde??)
    // public function add_size_grid_row_id_field()
    // {
    //     global $woocommerce, $post;
    //     echo '<div class="options_group">';
    //     woocommerce_wp_text_input(
    //         array(
    //             'id' => 'SIZE_GRID_ROW_ID',
    //             'label' => __('SIZE GRID ROW ID', 'woo-ml-sync'),
    //             'desc_tip' => 'true',
    //             'description' => __('Ingrese el ID de la fila de la grilla de tallas de MercadoLibre.', 'woo-ml-sync')
    //         )
    //     );
    //     echo '</div>';
    // }

    //Registra en el log los atributos del producto
    // private function log_product_attributes($product)
    // {
    //     $attributes = $product->get_attributes();
    //     $this->log_debug("Atributos del producto ID {$product->get_id()}:");
    //     foreach ($attributes as $attribute) {
    //         $this->log_debug("- Nombre: " . $attribute->get_name() . ", Valores: " . implode(', ', $attribute->get_options()));
    //     }
    // }

    //Pide la Guía de Tallas a Mercadolibre
    private function get_valid_size_grid_row_id($size_grid_id, $size)
    {
        $endpoint = WOO_ML_API_ENDPOINT . "/catalog/charts/" . $size_grid_id;

        $response = $this->make_api_request_sinLog($endpoint, 'GET');
        if (is_wp_error($response)) {
            $this->log_debug("Aqui está el error");
            $this->log_error("Error al obtener información de la grilla de tallas: " . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code === 200 && isset($body['rows'])) {
            foreach ($body['rows'] as $row) {
                foreach ($row['attributes'] as $attribute) {
                    if ($attribute['id'] === 'SIZE' && strtolower($attribute['values'][0]['name']) === strtolower($size)) {
                        $this->log_debug("SIZE GRID ROW ID encontrado: " . $row['id']);
                        return $row['id'];
                    }
                }
            }
            $this->log_debug("La talla no se encuentra en la size_grid");
        }
        $this->log_debug("Aca está el error");
        $this->log_error("No se encontró un SIZE_GRID_ROW_ID válido para SIZE_GRID_ID: $size_grid_id y talla: $size");
        return false;
    }

    /**
     * Importa las imágenes de un producto desde MercadoLibre y las asocia al producto en WooCommerce.
     *
     * Esta función obtiene las imágenes del producto de MercadoLibre a través de su API, verifica si
     * las imágenes ya están subidas en la biblioteca de medios de WordPress, y si no lo están, las sube
     * para asociarlas al producto de WooCommerce. Las imágenes se asignan tanto a la galería como a la
     * imagen principal del producto en WooCommerce.
     *
     * @param string $ml_id El ID del producto en MercadoLibre.
     * @param WC_Product $productoWooCommerce El objeto de producto de WooCommerce al que se asignarán las imágenes.
     *
     * @return bool Devuelve `true` si las imágenes fueron importadas y asociadas correctamente al producto,
     *              o `false` en caso de error. Si no se encontraron imágenes o hubo un problema al hacer
     *              la solicitud a la API de MercadoLibre, también se devuelve `false`.
     *
     * @throws Exception Si ocurre un error durante la solicitud a la API de MercadoLibre o en el procesamiento
     *                   de las imágenes, se puede lanzar una excepción.
     *
     * @since 1.0.0
     */
    public function importar_imagenes_mercadolibre($ml_id, $productoWooCommerce)
{
    // Verifica el token
    if (!$this->check_and_refresh_token()) {
        $this->log_error("No se pudo obtener un token de acceso válido.");
        return false;
    }

    // Endpoint para obtener detalles del producto
    $endpoint = WOO_ML_API_ENDPOINT . "/items/{$ml_id}?access_token=" . $this->access_token;
    $response = $this->make_api_request_sinLog($endpoint, 'GET');

    // Manejo de errores en la respuesta
    if (is_wp_error($response)) {
        $this->log_error("Error al obtener información del producto de MercadoLibre: " . $response->get_error_message());
        return false;
    }

    // Decodifica el JSON de la respuesta
    $productoMercadolibre = json_decode(wp_remote_retrieve_body($response), true);

    // Valida que el campo 'pictures' exista y no esté vacío
    if (empty($productoMercadolibre['pictures'])) {
        $this->log_debug("No se encontraron imágenes válidas para el producto {$ml_id}.");
        return false;
    }

    // Procesa cada imagen en 'pictures'
    $galeria = [];
    foreach ($productoMercadolibre['pictures'] as $imagen) {
        // Verifica que 'url' exista y sea válida
        if (!isset($imagen['url']) || empty($imagen['url'])) {
            $this->log_debug("Imagen inválida o sin URL en el producto {$ml_id}.");
            continue;
        }

        // Si MercadoLibre proporciona un id para la imagen
        if (isset($imagen['id']) && !empty($imagen['id'])) {
            $ml_picture_id = $imagen['id']; // id único de la imagen en MercadoLibre

            // Comprobar si la imagen ya está subida en la biblioteca de medios de WordPress
            $image_id = $this->buscar_imagen_por_ml_id($ml_picture_id);

            // Si la imagen ya existe, asigna el ID a la galería
            if ($image_id > 0) {
                $galeria[] = $image_id;
            } else {
                // Si la imagen no está subida, intenta subirla
                $image_id = media_sideload_image($imagen['url'], $productoWooCommerce->get_id(), null, 'id');

                // Manejo de errores al subir la imagen
                if (!is_wp_error($image_id)) {
                    $galeria[] = $image_id;
                    // GUARDA el meta con el nombre correcto para automatizar variaciones:
                    update_post_meta($image_id, '_ml_picture_id', $ml_picture_id);
                    $this->log_debug("Imagen subida exitosamente: {$imagen['url']} con ID de MercadoLibre {$ml_picture_id}");
                } else {
                    $this->log_error("Error al subir la imagen {$imagen['url']}: " . $image_id->get_error_message());
                }
            }
        } else {
            // Si no hay un ID de MercadoLibre, procesa como lo hacías antes (por URL)
            $image_id = attachment_url_to_postid($imagen['url']);

            // Si la imagen ya existe, asigna el ID a la galería
            if ($image_id > 0) {
                $galeria[] = $image_id;
                $this->log_debug("Imagen ya subida: {$imagen['url']} con ID {$image_id}");
            } else {
                // Si la imagen no está subida, intenta subirla
                $image_id = media_sideload_image($imagen['url'], $productoWooCommerce->get_id(), null, 'id');

                // Manejo de errores al subir la imagen
                if (!is_wp_error($image_id)) {
                    $galeria[] = $image_id;
                    $this->log_debug("Imagen subida exitosamente: {$imagen['url']}");
                } else {
                    $this->log_error("Error al subir la imagen {$imagen['url']}: " . $image_id->get_error_message());
                }
            }
        }
    }

    // Asocia las imágenes al producto en WooCommerce
    if (!empty($galeria)) {
        $productoWooCommerce->set_gallery_image_ids($galeria);
        $productoWooCommerce->save();
    } else {
        $this->log_debug("No se pudieron asociar imágenes al producto {$productoWooCommerce->get_id()}.");
    }

    // Asigna la primera imagen de la galería como la imagen principal
    if (!empty($galeria)) {
        $productoWooCommerce->set_image_id($galeria[0]);
        $productoWooCommerce->save();
    }

    return true;
}




    // function woo_actualizar_precio_producto($product_id, $price, $price_type = 'regular', $update_variations = false) {
    //     try {
    //         if (!function_exists('wc_get_product')) {
    //             throw new Exception('WooCommerce no está activo');
    //         }
    
    //         $product = wc_get_product($product_id);
            
    //         if (!$product) {
    //             throw new Exception("Producto con ID {$product_id} no encontrado");
    //         }
    
    //         $price = floatval($price);
    //         if ($price < 0) {
    //             throw new Exception("El precio no puede ser negativo");
    //         }
    
    //         $result = [
    //             'product_id' => $product_id,
    //             'product_type' => $product->get_type(),
    //             'actions' => []
    //         ];
    
    //         // Manejar diferentes tipos de producto
    //         switch ($product->get_type()) {
    //             case 'simple':
    //                 $this->update_simple_product_price($product, $price, $price_type);
    //                 $result['actions'][] = "Producto simple actualizado";
    //                 break;
    
    //             case 'variable':
    //                 if ($update_variations) {
    //                     $variations_updated = $this->update_variable_product_prices($product, $price, $price_type);
    //                     $result['variations_updated'] = $variations_updated;
    //                     $result['actions'][] = "Producto variable y sus variaciones actualizadas";
    //                 } else {
    //                     throw new Exception("Producto es variable. Use update_variations=true o actualice variaciones directamente");
    //                 }
    //                 break;
    
    //             case 'variation':
    //                 $this->update_variation_price($product, $price, $price_type);
    //                 $result['parent_id'] = $product->get_parent_id();
    //                 $result['actions'][] = "Variación actualizada";
    //                 break;
    
    //             default:
    //                 throw new Exception("Tipo de producto no soportado: " . $product->get_type());
    //         }
    
    //         // Limpieza de caché
    //         wc_delete_product_transients($product_id);
    //         if ($product->is_type('variation')) {
    //             clean_post_cache($product->get_parent_id());
    //         }
    //         clean_post_cache($product_id);
    
    //         $result['success'] = true;
    //         $result['new_price'] = $price;
    //         return $result;
    
    //     } catch (Exception $e) {
    //         return [
    //             'success' => false,
    //             'error' => $e->getMessage(),
    //             'product_id' => $product_id
    //         ];
    //     }
    // }
    
    // Métodos auxiliares
    private function update_simple_product_price($product, $price, $price_type) {
        if ($price_type === 'regular' || $price_type === 'both') {
            $product->set_regular_price($price);
        }
        if ($price_type === 'sale' || $price_type === 'both') {
            $product->set_sale_price($price);
        }
        $product->set_price($price); // Precio activo
        $product->save();
    }
    
    private function update_variation_price($variation, $price, $price_type) {
        if ($price_type === 'regular' || $price_type === 'both') {
            $variation->set_regular_price($price);
        }
        if ($price_type === 'sale' || $price_type === 'both') {
            $variation->set_sale_price($price);
        }
        $variation->set_price($price);
        $variation->save();
        wc_delete_product_transients($variation->get_id());
        wp_cache_flush();
        
        // Sincronizar padre
        $parent = wc_get_product($variation->get_parent_id());
        if ($parent) {
            $parent->variable_product_sync();
            $parent->save();
        }
    }
    
    private function update_variable_product_prices($product, $price, $price_type) {
        $variations = $product->get_children();
        $count = 0;
        
        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            if ($variation) {
                $this->update_variation_price($variation, $price, $price_type);
                $count++;
            }
        }
        
        // Actualizar precios del padre (rango)
        $product->variable_product_sync();
        $product->save();
        
        return $count;
    }
    /**
     * Función para buscar la imagen en la biblioteca de medios usando el ID de MercadoLibre.
     *
     * @param string $ml_image_id El ID de la imagen en MercadoLibre.
     * @return int El ID de la imagen en la biblioteca de medios de WordPress, o 0 si no se encuentra.
     */
    private function buscar_imagen_por_ml_id($ml_image_id)
    {
        // Busca el ID de la imagen en los metadatos de las imágenes (supone que el ID de MercadoLibre está guardado en '_ml_image_id')
        $args = [
            'post_type'  => 'attachment',
            'meta_key'   => '_ml_image_id',
            'meta_value' => $ml_image_id,
            'posts_per_page' => 1,
        ];

        $imagenes = get_posts($args);
        if (! empty($imagenes)) {
            $image_id  = $imagenes[0]->ID;
            $file_path = get_attached_file($image_id);
            if (file_exists($file_path)) {
                return $image_id;
            }
        }

        return 0; // Devuelve 0 si no la encuentra
    }

    public function borrarProductosWooCommerce()
    {
        //Verificar Nonce de Seguridad
        check_ajax_referer('borrarProductosWooCommerce_nonce', 'nonce');

        // Obtener todos los productos de WooCommerce
        $args = [
            'post_type' => 'product',
            'post_status' => 'any',
            'numberposts' => -1
        ];
        $products = get_posts($args);

        if (count(get_posts($args)) < 1) {
            wp_send_json_error('No se han encontrado productos en WooCommerce para eliminar.');
        }

        // Eliminar cada producto
        foreach ($products as $product) {
            wp_delete_post($product->ID, true);
        }

        wp_send_json_success('Se han eliminado todos los productos de WooCommerce con éxito.');
    }

    public function sync_stock_mercadolibre($notificacion)
    {
        //Obtener producto de Mercadolibre
        $endpoint = WOO_ML_API_ENDPOINT . $notificacion['resource'] . "?include_attributes=all";
        $response = $this->make_api_request_v2($endpoint, 'GET');
        $ml_product = json_decode(wp_remote_retrieve_body($response), true);

        $this->log_debug("********************");
        $this->log_debug("Se ha registrado una venta en Mercadolibre del producto " . $ml_product['id'] . ". Actualizando stock en WooCommerce.");

        //Obtener precios de ofertas
        $endpoint_price = WOO_ML_API_ENDPOINT . "/items/" . $ml_product['id'] . "/sale_price";
        $response_price = $this->make_api_request($endpoint_price, 'GET');
        $ml_prices = json_decode(wp_remote_retrieve_body($response_price), true);
        if ($ml_prices['regular_amount'] == null) {
            $ml_prices['regular_amount'] = $ml_prices['amount'];
        }

        //Obtener producto de WooCommerce
        $this->log_debug("Seller Custom Field: " . $ml_product['seller_custom_field']);
        $wc_product_id = wc_get_product_id_by_sku($ml_product['seller_custom_field']);
        $wc_product = wc_get_product($wc_product_id);
        $this->log_debug("ID del producto en WooCommerce: " . $wc_product_id);

        if ($wc_product->is_type('variable')) {
            //Obtener variaciones de mercadolibre y woocommerce
            foreach ($ml_product['variations'] as $variation) {
                $ml_stock = $variation['available_quantity'];
                $ml_sku = array_column($variation['attributes'], 'value_name', 'id')['SELLER_SKU'] ?? null;
                $wc_variation_id = wc_get_product_id_by_sku($ml_sku);
                $wc_variation = wc_get_product($wc_variation_id);
                $wc_variation->set_stock_quantity($ml_stock);
                $wc_variation->set_regular_price($ml_prices['regular_amount']);
                $wc_variation->set_sale_price($ml_prices['amount']);
                $wc_variation->save();
                wc_delete_product_transients($variation->get_id());
                wp_cache_flush();
                $this->log_debug("Variacion " . $ml_sku . "sincronizada correctamente");
            }
        }
        //Si el producto no tiene variaciones, se asigna el stock directo al producto
        $wc_product->set_stock_quantity($ml_product['available_quantity']);
        $wc_product->set_regular_price($ml_prices['regular_amount']);
        $wc_product->set_sale_price($ml_prices['amount']);
        $wc_product->save();

        $this->log_debug("Stock sincronizado para el producto ID: {$wc_product_id}");
        $this->log_debug("********************");
    }

    /**
     * Inicializa los hooks necesarios para registrar las rutas REST.
     */
    public function init_internal_api()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Registra las rutas de la API REST.
     */
    public function register_routes()
    {
        $namespace = 'mlsink/v1';
        register_rest_route(
            $namespace,
            '/notificaciones',
            array(
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array($this, 'handle_notifications'),
                    'permission_callback' => array($this, 'permission_check'),
                    'args'                => array(),
                ),
            )
        );
    }

    /**
     * Maneja las notificaciones entrantes desde Mercado Libre.
     *
     * @param WP_REST_Request $request Objeto de la solicitud entrante.
     *
     * @return WP_REST_Response Respuesta de la API REST.
     */
    public function handle_notifications($request)
    {
        $data = $request->get_json_params();

        $this->log_debug('MANEJANDO NOTIFICACION: ' . json_encode($data));

        // Validar que el campo 'topic' exista.
        if (! isset($data['topic'])) {
            return new WP_REST_Response(array(
                'error' => 'El campo "topic" es obligatorio.',
            ), 400);
        }

        $topic = sanitize_text_field($data['topic']);

        switch ($topic) {
            case 'items':
                return $this->handle_items($data);
            default:
                return new WP_REST_Response(array(
                    'error' => 'El topic especificado no es soportado.',
                ), 400);
        }
    }

    /**
     * Maneja las notificaciones del tipo 'items'.
     *
     * @param array $data Datos de la notificación.
     *
     * @return WP_REST_Response Respuesta de la API REST.
     */
    private function handle_items($data)
    {
        // Lógica para procesar el topic 'items'.
        $resource = $data['resource'] ?? 'No especificado';
        $user_id  = $data['user_id'] ?? 'No especificado';

        $this->sync_stock_mercadolibre($data);

        return new WP_REST_Response(array(
            'success'  => true,
            'message'  => 'Notificación de items procesada.',
            'resource' => $resource,
            'user_id'  => $user_id,
        ), 200);
    }

    /**
     * Verifica si el usuario tiene permisos para acceder al endpoint.
     *
     * @return bool Retorna verdadero si se permite el acceso.
     */
    public function permission_check()
    {
        // Permitir acceso sin autenticar para recibir notificaciones.
        return true;
    }

    /**
     * Obtener sku formatiado y valido
     *
     * @param string $sku El sku
     */
    // public function sanitize_sku($sku)
    // {
    //     $sku = trim($sku);
    //     $sku = strtoupper($sku);

    //     return (string)$sku;
    // }

    /**
     * Encuentra el id de un producto de mercado libre usando el sku de woocomerce
     * y el `seller_custom_field` de mercadolibre.
     *
     * @param string $sku
     *
     * @return string|WP_Error
     */
    public function get_simple_product_ml_id_by_sku($sku)
    {
        if (!$this->check_and_refresh_token()) {
            $this->log_error("Problemas al verificar o refrescar el token");
            return new WP_Error('invalid_token', ('Error: Token inválido o caducado.'));
        }

        $user_endpoint = WOO_ML_API_ENDPOINT . '/users/me';
        $user_response = $this->make_api_request_v2($user_endpoint, 'GET');
        if (is_wp_error($user_response)) {
            $this->log_error("Problemas al obtener el usuario");
            return false;
        }

        $user_body = json_decode(wp_remote_retrieve_body($user_response), true);
        $user_id = $user_body['id'];

        $endpoint = WOO_ML_API_ENDPOINT . "/users/$user_id/items/search?sku=$sku";
        $response = $this->make_api_request_v2($endpoint, 'GET');
        if (is_wp_error($response)) {
            $this->log_error("Problemas al obtener el item por sku");
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        $ml_id = $data['results'][0];
        return $ml_id;
    }

    /**
     * @param string $ml_parent_id
     * @param string $sku
     *
     * @return string|WP_Error El id de la variacion de mercado libre o un error si nose encuentra
     */
    public function get_variation_ml_id_by_sku($ml_parent_id, $sku)
    {
        $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_parent_id/variations?include_attributes=all";

        $response = $this->make_api_request_v2($endpoint, 'GET');
        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! empty($data)) {
            foreach ($data as $variation) {
                if (! isset($variation['attributes'])) {
                    continue;
                }

                foreach ($variation['attributes'] as $attribute) {
                    if ($attribute['id'] === 'SELLER_SKU') {
                        $ml_sku = $attribute['value_name'];
                        $wc_sku = $sku;
                        if ($ml_sku === $wc_sku) {
                            return $variation['id'];
                        }
                    }
                }
            }
        }

        return new WP_Error('id_not_founded', 'El id de mercadolibre para la variacion no se pudo encontrar.');
    }

    /**
     * Extrae combinaciones de atributos de una lista de elementos y los agrupa por un identificador común.
     *
     * @since 27.01.25
     * @param array $items Lista de elementos con combinaciones de atributos.
     * @param string $name_key Clave que identifica el nombre del atributo (por defecto: 'name').
     * @param string $value_key Clave que identifica el valor del atributo (por defecto: 'value_name').
     * @return array Lista agrupada de atributos con sus valores únicos.
     */
    // public function extract_attributes($items, $name_key = 'name', $value_key = 'value_name')
    // {
    //     $result = [];

    //     foreach ($items as $item) {

    //         foreach ($item as $attr) {
    //             if (!isset($attr[$name_key]) || !isset($attr[$value_key])) continue; // Saltar si faltan claves

    //             $name = $attr[$name_key];
    //             $value = $attr[$value_key];

    //             // Si el nombre del atributo no está en el resultado, inicializarlo
    //             if (!isset($result[$name])) {
    //                 $result[$name] = [
    //                     $name_key => $name,
    //                     'values' => []
    //                 ];
    //             }

    //             // Evitar valores duplicados
    //             if (!in_array($value, $result[$name]['values'])) {
    //                 $result[$name]['values'][] = $value;
    //             }
    //         }
    //     }

    //     // Convertir el array asociativo en un array indexado
    //     return array_values($result);
    // }

    /**
     * Crea o actualiza los atributos de un producto en WooCommerce basados en una lista de atributos personalizados.
     *
     * @since 27.01.25
     * @param WC_Product $wc_product El objeto de producto de WooCommerce que se va a actualizar.
     * @param array $attributes Lista de atributos en formato:
     *                          [['name' => '...', 'values' => ['...', '...']], ...]
     * @param array $options Opciones adicionales para el método:
     *                        - 'save' (bool): Indica si se debe guardar el producto automáticamente. Por defecto, true.
     *                        - 'variation' (bool): Indica si el atributo es usado para variaciones. Por defecto, false.
     *
     * @return true|WP_Error Retorna true si la operación fue exitosa o un WP_Error si ocurrió un problema.
     */
    // public function create_or_update_attributes_product_v2($wc_product, $attributes, $options = ['save' => true, 'variation' => false])
    // {
    //     $wc_attributes = $wc_product->get_attributes();

    //     foreach ($attributes as $attribute) {
    //         if (!isset($attribute['name']) || !isset($attribute['values'])) continue;

    //         $nombre_atributo = sanitize_text_field($attribute['name']);
    //         $taxonomy_name = wc_sanitize_taxonomy_name($nombre_atributo);
    //         $attribute_id = wc_attribute_taxonomy_id_by_name($taxonomy_name);

    //         // Si el atributo no existe, crearlo
    //         if (!$attribute_id) {
    //             $this->log_debug("Creando atributo global: " . $nombre_atributo);
    //             $attribute_id = wc_create_attribute([
    //                 'name' => $nombre_atributo,
    //                 'slug' => sanitize_title($nombre_atributo),
    //                 'type' => 'select'
    //             ]);
    //             flush_rewrite_rules(); // Actualiza atributos globales
    //         }

    //         // Obtener la taxonomía asociada
    //         $taxonomy = wc_attribute_taxonomy_name_by_id($attribute_id);
    //         $terms_ids = [];

    //         foreach ($attribute['values'] as $value) {
    //             $option = sanitize_text_field($value);
    //             if (!empty($option)) {
    //                 // Buscar o insertar el término
    //                 $term = get_term_by('name', $option, $taxonomy);
    //                 if (!$term || is_wp_error($term)) {
    //                     $term = wp_insert_term($option, $taxonomy);
    //                 }

    //                 // Si el término es válido, agregarlo
    //                 if (!is_wp_error($term) && isset($term->term_id)) {
    //                     $terms_ids[] = $term->term_id;
    //                 } else {
    //                     $this->log_error("Problema al agregar el valor: " . $option . " al atributo global" . $taxonomy);
    //                 }
    //             }
    //         }

    //         // Obtener atributos existentes en el producto y fusionar valores nuevos
    //         if (isset($wc_attributes[$taxonomy])) {
    //             $existing_attribute = $wc_attributes[$taxonomy];
    //             $existing_terms = $existing_attribute->get_options() ?? [];
    //             $terms_ids = array_unique(array_merge($existing_terms, $terms_ids));
    //         }

    //         // Crear o actualizar el atributo en el producto
    //         $product_attribute = new WC_Product_Attribute();
    //         $product_attribute->set_id($attribute_id);
    //         $product_attribute->set_name($taxonomy);
    //         $product_attribute->set_options($terms_ids);
    //         $product_attribute->set_visible(true);
    //         $product_attribute->set_variation($options['variation']);
    //         $wc_attributes[$taxonomy] = $product_attribute;
    //     }

    //     // Guardar atributos sin sobrescribir valores existentes
    //     $wc_product->set_attributes($wc_attributes);
    //     if ($options['save']) {
    //         $wc_product->save();
    //     }

    //     $this->log_debug("Atributos globales de variaciones actualizados");
    //     return true;
    // }

    /**
     * Extrae y combina todas las combinaciones de atributos de MercadoLibre.
     *
     * @param array $ml_data Datos del producto de MercadoLibre que contienen variaciones.
     * @return array Lista combinada de atributos en formato [['name' => '...', 'value_name' => '...'], ...].
     */
    // public function extract_attribute_combinations($ml_data)
    // {
    //     $attributes = [];

    //     foreach ($ml_data as $variation) {
    //         if (!isset($variation['attribute_combinations']) || !is_array($variation['attribute_combinations'])) {
    //             continue;
    //         }

    //         foreach ($variation['attribute_combinations'] as $attribute) {
    //             if (isset($attribute['name']) && isset($attribute['value_name'])) {
    //                 $attributes[] = [
    //                     'name'       => sanitize_text_field($attribute['name']),
    //                     'value_name' => sanitize_text_field($attribute['value_name']),
    //                 ];
    //             }
    //         }
    //     }

    //     return $attributes;
    // }

    /**
     * Sube imágenes a MercadoLibre para productos simples, productos padres y variaciones.
     *
     * @since 27.01.25
     * @param WC_Product $product El objeto del producto de WooCommerce.
     * @return array|WP_Error Un array de IDs de imágenes subidas o un objeto WP_Error en caso de error.
     */
    // public function upload_images_to_mercadolibre($product)
    // {
    //     $this->log_debug("Subiendo imágenes para el producto ID: {$product->get_id()}");

    //     // Verifica el token
    //     if (!$this->check_and_refresh_token()) {
    //         return new WP_Error('token_error', __('No se pudo obtener un token de acceso válido.', 'woo-ml-sync'));
    //     }

    //     // Obtiene las imágenes desde los metadatos del producto
    //     $metadatos = get_post_meta($product->get_id(), '_datos_mercadolibre', true);
    //     if (empty($metadatos) || empty($metadatos['pictures'])) {
    //         return new WP_Error('no_images', __('No se encontraron imágenes en los metadatos del producto.', 'woo-ml-sync'));
    //     }

    //     $images = $metadatos['pictures'];
    //     $uploaded_images = array();

    //     foreach ($images as $image) {
    //         $image_url = $image['url'];
    //         $endpoint = WOO_ML_API_ENDPOINT . '/pictures';
    //         $data = array('source' => $image_url);

    //         $response = $this->make_api_request_v2($endpoint, 'POST', $data);

    //         if (is_wp_error($response)) {
    //             $this->log_error("Error al subir la imagen a MercadoLibre: " . $response->get_error_message());
    //             continue;
    //         }

    //         $body = json_decode(wp_remote_retrieve_body($response), true);

    //         if (isset($body['id'])) {
    //             $uploaded_images[] = $body['id'];
    //         } else {
    //             $this->log_error("Error desconocido al subir la imagen: " . print_r($body, true));
    //         }
    //     }

    //     if (empty($uploaded_images)) {
    //         return new WP_Error('upload_error', __('No se pudieron subir las imágenes a MercadoLibre.', 'woo-ml-sync'));
    //     }

    //     // Asigna las imágenes subidas al producto en MercadoLibre
    //     $ml_product_id = $this->get_simple_product_ml_id_by_sku($product->get_sku());
    //     if (!$ml_product_id) {
    //         return new WP_Error('ml_product_not_found', __('El producto no está sincronizado con MercadoLibre.', 'woo-ml-sync'));
    //     }

    //     $data = array('pictures' => array_map(function ($id) {
    //         return array('id' => $id);
    //     }, $uploaded_images));

    //     $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_product_id";
    //     $response = $this->make_api_request_v2($endpoint, 'PUT', $data);

    //     if (is_wp_error($response)) {
    //         return $response;
    //     }

    //     $this->log_debug("Imágenes subidas y asignadas correctamente al producto ID: {$product->get_id()} en MercadoLibre.");

    //     return $uploaded_images;
    // }

    /**
     * Crea o actualiza un producto en MercadoLibre.
     *
     * - Si el producto ya existe en MercadoLibre (según su SKU), se actualiza.
     * - Si no existe, se crea como un nuevo producto.
     *
     * @param WC_Product $wc_product
     * @return int|WP_Error
     */
    // public function create_or_update_product_to_mercadolibre($wc_product)
    // {
    //     $wc_id   = $wc_product->get_id();
    //     $ml_data = get_post_meta($wc_id, '_datos_mercadolibre', true);

    //     $sku = $wc_product->get_sku();
    //     if (empty($sku)) {
    //         $this->log_debug("[INFO] Producto ID ({$wc_id}) sin SKU. Abortando sincronización.");
    //         return new WP_Error('missing_sku', 'El producto no tiene SKU.');
    //     }

    //     // Buscar si el producto ya está en MercadoLibre
    //     $ml_id_res = $this->get_simple_product_ml_id_by_sku($sku);
    //     if (is_wp_error($ml_id_res)) {
    //         if (strtoupper((string)$ml_id_res->get_error_code()) === 'ML_ID_NOT_FOUND') {
    //             $this->log_debug("[INFO] creando el producto ({$wc_id} - {$sku}) en mercadolibre ({$ml_id_res})");
    //             return $this->create_product_to_mercadolibre($wc_product, $ml_data);
    //         }
    //         $this->log_debug("[ERROR] No se pudo sincronizar el producto ({$wc_id}). " . json_encode($ml_id_res));
    //         return $ml_id_res;
    //     }

    //     // Si el producto existe en ML, actualizarlo
    //     $this->log_debug("[INFO] actualizando ({$wc_id} - {$sku}) en mercadolibre ({$ml_id_res})");
    //     return $this->update_product_to_mercadolibre($ml_id_res, $wc_product, $ml_data);
    // }

    /**
     * Crea un producto en MercadoLibre.
     *
     * - Limpia los datos antes de enviarlos.
     * - Realiza la petición a la API de MercadoLibre.
     *
     * @param WC_Product $wc_product
     * @param array $ml_data Datos adicionales del producto.
     * @return int|WP_Error
     */
    public function create_product_to_mercadolibre($wc_product, $ml_data)
    {
        $clean_data = $this->ml_on_create_cleanator($ml_data);

        $endpoint = WOO_ML_API_ENDPOINT . '/items';
        $response = $this->make_api_request_v2($endpoint, 'POST', $clean_data);

        if (is_wp_error($response)) {
            $this->log_debug("[ERROR] Error al crear producto en ML: " . json_encode($response));
            return $response;
        }

        $this->log_debug("[SUCCESS] Producto creado en MercadoLibre con ID: " . $response['id']);

        return $response['id'];
    }

    /**
     * Actualiza un producto existente en MercadoLibre.
     *
     * - Obtiene los datos actuales del producto desde MercadoLibre.
     * - Fusiona los datos existentes con los nuevos datos (`ml_merge_ids`).
     * - Limpia los datos fusionados para evitar modificar campos no permitidos.
     * - Envía una solicitud `PUT` a la API de MercadoLibre.
     *
     * @param string $ml_id ID del producto en MercadoLibre.
     * @param WC_Product $wc_product Producto en WooCommerce.
     * @param array $ml_data Datos del producto a actualizar.
     * @return int|WP_Error
     */
    public function update_product_to_mercadolibre($ml_id, $wc_product, $ml_data)
    {
        $endpoint = WOO_ML_API_ENDPOINT . "/items/$ml_id/?include_attributes=all";
        $response = $this->make_api_request_v2($endpoint, 'GET');
        if (is_wp_error($response)) {
            $this->log_debug('[ERROR] no se pudo obtener data del producto a actualizar ');
            return $response;
        }

        $ml_data2 = json_decode(wp_remote_retrieve_body($response), true);

        // Fusionar datos existentes con nuevos
        $merged_data = $this->ml_merge_ids($ml_data2, $ml_data);

        // Limpiar los datos para evitar modificar campos no permitidos
        $clean_data = $this->ml_on_update_cleanator($merged_data);
        $this->log_debug('f3 ' . json_encode($clean_data));

        // Endpoint para actualizar el producto
        $endpoint = WOO_ML_API_ENDPOINT . "/items/{$ml_id}";
        $response = $this->make_api_request_v2($endpoint, 'PUT', $clean_data);

        if (is_wp_error($response)) {
            $this->log_debug("[ERROR] Error al actualizar producto ML ID ({$ml_id}): " . json_encode($response));
            return $response;
        }

        $this->log_debug("[SUCCESS] Producto ML ID ({$ml_id}) actualizado correctamente.");

        return $ml_id;
    }

    /**
     * Limpia los datos del producto para actualización en MercadoLibre.
     *
     * En la actualización de productos, ciertos campos como el título, la categoría
     * y otros no pueden ser modificados, por lo que esta función los elimina
     * para evitar errores en la API.
     *
     * @param array $ml_product_data Datos del producto obtenidos desde MercadoLibre.
     * @return array Datos limpios listos para actualizar un producto.
     */
    function ml_on_update_cleanator($ml_product_data)
    {
        // FIXME: durante las pruebas la cuenta se bloqueo por lo que arrojo todo invalido
        //        por lo tanto debemos limpiar esto solo con data que no se puede modificar
        // Campos que no se pueden modificar en una actualización
        $unmodifiable_fields = [
            'title',
            'status',
            'family_name',
            'channels',
            'end_time',
            'seller_address.comment',
            'shipping.local_pick_up',
            'description.snapshot.height',
            'stop_time',
            'last_updated',
            'description.plain_text',
            'thumbnail',
            'seller_id',
            'seller_address.id',
            'seller_address.country',
            'listing_type_id',
            'description.snapshot.width',
            'sale_terms',
            'geolocation',
            'site_id',
            'domain_id',
            'seller_address.latitude',
            'seller_address.state',
            'accepts_mercadopago',
            'geolocation.latitude',
            'id',
            'expiration_time',
            'description.snapshot',
            'description.last_updated',
            'description.snapshot.url',
            'seller_address.search_location.state',
            'seller_address.country.name',
            'shipping.free_shipping',
            'price',
            'description',
            'tags',
            'shipping.dimensions',
            'seller_address.city.name',
            'seller_address.search_location.city',
            'seller_address.address_line',
            'seller_address.city',
            'attributes',
            'seller_address.search_location.city.id',
            'user_product_id',
            'seller_address.country.id',
            'seller_address.state.id',
            'seller_address.city.id',
            'catalog_listing',
            'shipping.mode',
            'condition',
            'seller_address.search_location',
            'shipping',
            'start_time',
            'health',
            'base_price',
            'shipping.store_pick_up',
            'seller_address.longitude',
            'shipping.methods',
            'seller_address.search_location.city.name',
            'seller_address.search_location.state.id',
            'shipping.logistic_type',
            'shipping.tags',
            'seller_address.state.name',
            'automatic_relist',
            'initial_quantity',
            'seller_address',
            'thumbnail_id',
            'description.date_created',
            'sold_quantity',
            'geolocation.longitude',
            'buying_mode',
            'warranty',
            'date_created',
            'international_delivery_mode',
            'permalink',
            'currency_id',
            'description.snapshot.status',
            'seller_address.search_location.state.name'
        ];

        // Eliminar los campos no modificables (incluyendo campos anidados)
        foreach ($unmodifiable_fields as $field) {
            $keys = explode('.', $field); // Divide el campo en partes (por el punto)
            $temp = &$ml_product_data; // Referencia temporal al array principal

            // Recorre el array anidado
            foreach ($keys as $key) {
                if (isset($temp[$key])) {
                    if (end($keys) === $key) {
                        // Si es el último nivel, elimina el campo
                        unset($temp[$key]);
                    } else {
                        // Avanza al siguiente nivel del array
                        $temp = &$temp[$key];
                    }
                } else {
                    // Si el campo no existe, sal del bucle
                    break;
                }
            }
        }

        // Eliminar cualquier campo con valores no deseados (null, "", [])
        $ml_product_data = array_filter($ml_product_data, function ($value) {
            return !is_null($value) && $value !== '' && $value !== [];
        });

        return $ml_product_data;
    }

    /**
     * Limpia los datos del producto para la creación en MercadoLibre.
     *
     * - Elimina IDs internos y metadatos innecesarios.
     * - Remueve IDs de variaciones para evitar conflictos.
     * - Se asegura de que no haya enlaces permanentes.
     * - Limpia los IDs de las imágenes pero mantiene las URLs.
     *
     * @param array $ml_product_data Datos del producto obtenidos desde MercadoLibre.
     * @return array Datos limpios listos para crear un nuevo producto.
     */
    function ml_on_create_cleanator($ml_product_data)
    {
        $fields_to_remove = [
            'id',
            'catalog_product_id',
            'permalink',
            'thumbnail_id',
            'date_created',
            'last_updated',
            'seller_id',
            'official_store_id',
            'secure_thumbnail',
            'sold_quantity',
            'status'
        ];

        $variation_fields_to_remove = [
            'id',
            'inventory_id',
            'seller_custom_field',
            'variation_id'
        ];

        // Eliminar los campos no necesarios a nivel producto
        foreach ($fields_to_remove as $field) {
            unset($ml_product_data[$field]);
        }

        // Limpiar los atributos (remover los IDs internos)
        if (!empty($ml_product_data['attributes'])) {
            foreach ($ml_product_data['attributes'] as &$attr) {
                unset($attr['id']);
            }
        }

        // Limpiar las variaciones
        if (!empty($ml_product_data['variations'])) {
            foreach ($ml_product_data['variations'] as &$variation) {
                foreach ($variation_fields_to_remove as $field) {
                    unset($variation[$field]);
                }
                // Remover IDs en los atributos de la variación
                if (!empty($variation['attributes'])) {
                    foreach ($variation['attributes'] as &$attr) {
                        unset($attr['id']);
                    }
                }
            }
        }

        // Limpiar las imágenes, eliminando el ID pero manteniendo la URL
        if (!empty($ml_product_data['pictures'])) {
            foreach ($ml_product_data['pictures'] as &$picture) {
                unset($picture['id']); // Eliminar ID de la imagen
            }
        }

        // Eliminar cualquier campo con valores no deseados (null, "", [])
        $ml_product_data = array_filter($ml_product_data, function ($value) {
            return !is_null($value) && $value !== '' && $value !== [];
        });

        return $ml_product_data;
    }

    /**
     * Fusiona dos conjuntos de datos de productos de MercadoLibre y mantiene solo un ID final.
     *
     * - Si ambos productos tienen ID, se prioriza el del primer conjunto.
     * - Fusiona variaciones sin duplicar IDs.
     * - Extrae correctamente los SKUs desde atributos y variaciones.
     *
     * @param array $ml_data1 Primer conjunto de datos del producto.
     * @param array $ml_data2 Segundo conjunto de datos del producto.
     * @return array Producto fusionado con IDs unificados.
     */
    function ml_merge_ids($ml_data1, $ml_data2)
    {
        $merged_product = array_merge($ml_data1, $ml_data2);

        // Mantener solo un ID
        $merged_product['id'] = $ml_data1['id'] ?? $ml_data2['id'] ?? null;

        // Fusionar variaciones sin duplicar IDs
        if (!empty($ml_data1['variations']) || !empty($ml_data2['variations'])) {
            $variations = [];

            foreach (array_merge($ml_data1['variations'] ?? [], $ml_data2['variations'] ?? []) as $variation) {
                $sku = $this->extract_sku_from_variation($variation);
                $key = $sku ?: json_encode($variation['attributes'] ?? []);

                if (!isset($variations[$key])) {
                    $variations[$key] = $variation;
                } else {
                    // Priorizar ID de la primera data
                    if (!empty($variation['id']) && empty($variations[$key]['id'])) {
                        $variations[$key]['id'] = $variation['id'];
                    }
                }
            }

            $merged_product['variations'] = array_values($variations);
        }

        // Extraer y asignar SKU para productos sin variaciones
        if (empty($merged_product['variations'])) {
            $merged_product['seller_custom_field'] = $this->extract_sku_from_simple($merged_product);
        }

        return $merged_product;
    }

    /**
     * Extrae el SKU de un producto sin variaciones.
     *
     * - Busca el atributo `SELLER_SKU` en la lista de atributos.
     * - Si lo encuentra, devuelve su `value_name`.
     *
     * @param array $ml_product_data Datos del producto.
     * @return string|null SKU extraído o null si no se encuentra.
     */
    function extract_sku_from_simple($ml_product_data)
    {
        if (!empty($ml_product_data['attributes'])) {
            $seller_sku_filter = array_filter($ml_product_data['attributes'], fn($attr) => $attr['id'] === 'SELLER_SKU');
            $seller_sku = reset($seller_sku_filter);
            return $seller_sku['value_name'] ?? null;
        }
        return null;
    }

    /**
     * Extrae el SKU de una variación.
     *
     * - Busca el atributo `SELLER_SKU` dentro de la variación.
     * - Si lo encuentra, devuelve su `value_name`.
     *
     * @param array $ml_variation Datos de la variación.
     * @return string|null SKU extraído o null si no se encuentra.
     */
    function extract_sku_from_variation($ml_variation)
    {
        if (!empty($ml_variation['attributes'])) {
            $seller_sku_filter = array_filter($ml_variation['attributes'], fn($attr) => $attr['id'] === 'SELLER_SKU');
            $seller_sku = reset($seller_sku_filter);
            return $seller_sku['value_name'] ?? null;
        }
        return null;
    }
    
    /* Función para cargar los productos en el nav->#productos */
    public function get_synced_products_callback()
    {
        // Verificación de seguridad
        check_ajax_referer('get_synced_products_nonce', 'nonce');

        // Manejo del token de acceso
        if (!$this->check_and_refresh_token()) {
            $this->log_error('get_synced_products: Fallo al refrescar el token de acceso.');
            wp_send_json_error(['message' => 'No se pudo obtener un token de acceso válido.']);
            return;
        }

        try {
            // --- INICIO DE PETICIONES A LA API ---

            // Obtener el ID del vendedor mediante llamada a la API
            $user_endpoint = WOO_ML_API_ENDPOINT . '/users/me';
            $user_response = $this->make_api_request_sinLog($user_endpoint, 'GET');
            
            // Si la llamada da error
            if (is_wp_error($user_response) || wp_remote_retrieve_response_code($user_response) !== 200) {
                throw new Exception('No se pudo obtener la información del usuario de Mercado Libre.');
            }
            // De lo contrario se guarda en una lista
            $user_body = json_decode(wp_remote_retrieve_body($user_response), true);
            $seller_id = $user_body['id'];

            // Obtener la lista de IDs de productos del vendedor (paginado)
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1; // Determinar página
            $limit = 20; // Límite de productos por página para no saturar
            $offset = ($page - 1) * $limit; // Calcular desde donde empieza a obtener productos
            $items_list_endpoint = WOO_ML_API_ENDPOINT . "/users/{$seller_id}/items/search?status=active&limit={$limit}&offset={$offset}";
            $items_list_response = $this->make_api_request_sinLog($items_list_endpoint, 'GET'); // Obtener la lista
            
            // Verificar errores
            if (is_wp_error($items_list_response) || wp_remote_retrieve_response_code($items_list_response) !== 200) {
                throw new Exception('No se pudo obtener la lista de productos de Mercado Libre.');
            }
            // Guardar la respuesta
            $items_list_body = json_decode(wp_remote_retrieve_body($items_list_response), true);
            $ml_ids = $items_list_body['results'] ?? [];
            $total_ml_items = $items_list_body['paging']['total'] ?? 0; // Total de productos
            $total_pages = ceil($total_ml_items / $limit); // Número de páginas a cargar
            $productos_formateados = array(); // Array para guardar lo necesario de los productos

            // Si no esta vacío
            if (!empty($ml_ids)) {
                // Obtener los detalles de todos los productos en una sola llamada
                $ids_string = implode(',', $ml_ids);
                $details_endpoint = WOO_ML_API_ENDPOINT . "/items?ids={$ids_string}&attributes=id,title,permalink,thumbnail,seller_custom_field";
                $details_response = $this->make_api_request_sinLog($details_endpoint, 'GET');
                
                // Si llamada da error, devuelve una respuesta
                if (is_wp_error($details_response) || wp_remote_retrieve_response_code($details_response) !== 200) {
                    throw new Exception('No se pudieron obtener los detalles de los productos de Mercado Libre.');
                }
                $ml_products_details = json_decode(wp_remote_retrieve_body($details_response), true); // Obtener respuesta
                
                // Si la llamada es nula, da error también
                if ($ml_products_details === null) {
                    throw new Exception('La respuesta de la API para los detalles de productos es nula.');
                }

                // Recorrer los resultados y formatear la información
                foreach ($ml_products_details as $item) {
                    $ml_product = $item['body']; // Destalles del producto
                    $sku = $ml_product['seller_custom_field'] ?? 'N/A'; // SKU, si no tiene N/A

                    // Buscar el producto en WooCommerce por SKU
                    $wc_product_id = !empty($sku) ? wc_get_product_id_by_sku($sku) : 0;
                    $wc_product = $wc_product_id ? wc_get_product($wc_product_id) : null;
                    
                    // Poner productos en una lista
                    $productos_formateados[] = [
                        'image' => esc_url($ml_product['thumbnail']),
                        'id' => $wc_product ? $wc_product->get_id() : 'N/A',
                        'name' => esc_html($ml_product['title']),
                        'sku' => esc_html($sku),
                        'permalink_ml' => esc_url($ml_product['permalink']),
                        'permalink_wc' => $wc_product ? get_edit_post_link($wc_product->get_id()) : '#',
                        'permalink_page' => $wc_product ? $wc_product->get_permalink() : '#',
                    ];
                }
            }

            // Contar productos de WooCommerce (Nota: puede ser lento en sitios muy grandes)
            $total_wc_items = count(wc_get_products(['limit' => -1, 'return' => 'ids']));

            // Enviar la respuesta correcta al frontend
            wp_send_json_success([
                'products' => $productos_formateados,
                'total_pages' => $total_pages,
                'total_ml_items' => $total_ml_items,
                'total_wc_items' => $total_wc_items,
            ]);

        } catch (Exception $e) {
            // Manejo de errores centralizado
            $this->log_error('get_synced_products: ' . $e->getMessage());
            wp_send_json_error(['message' => 'Error al procesar la solicitud: ' . $e->getMessage()]);
        }
    }
}

    function woo_ml_init()
    {
        new WooMercadoLibreSync();
    }
    add_action('plugins_loaded', 'woo_ml_init');
