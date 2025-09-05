<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="woo-ml-admin">
    <header class="woo-ml-header">
        <h1>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                <path fill="none" d="M0 0h24v24H0z" />
                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm1-8h4v2h-6V7h2v5z" fill="currentColor" />
            </svg>
            WooCommerce MercadoLibre Sync
        </h1>
    </header>

    <nav class="woo-ml-tabs" role="tablist">
        <a href="#settings" class="woo-ml-tab active" data-tab="settings" role="tab" aria-selected="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                <path fill="none" d="M0 0h24v24H0z" />
                <path d="M12 1l9.5 5.5v11L12 23l-9.5-5.5v-11L12 1zm0 2.311L4.5 7.653v8.694l7.5 4.342 7.5-4.342V7.653L12 3.311zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm0-2a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
            </svg>
            Configuración
        </a>
        <a href="#products" class="woo-ml-tab" data-tab="products" role="tab" aria-selected="false">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                <path fill="none" d="M0 0h24v24H0z" />
                <path d="M3 10h18v10h-18v-10zm0-2v-5h18v5h-18zm2-1h14v-2h-14v2zm10 10h2v-6h-2v6zm-4 0h2v-6h-2v6zm-4 0h2v-6h-2v6zm-4 0h2v-6h-2v6z" fill="currentColor" />
            </svg>
            Productos
        </a>
     
     
     <a href="#auditoria-tab" class="woo-ml-tab" data-tab="auditoria" role="tab" aria-selected="false">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
        <path fill="none" d="M0 0h24v24H0z" />
        <path d="M3 4v16h18V4H3zm2 2h14v12H5V6zm2 2v2h10V8H7zm0 4v2h7v-2H7z" fill="currentColor" />
    </svg>
    Auditoría ML
</a>
        


    </nav>
    
    

    <div class="woo-ml-messages" aria-live="polite">
        <?php
        $error_messages = get_settings_errors('woo_ml_messages');
        foreach ($error_messages as $message) {
            $class = ($message['type'] === 'error') ? 'woo-ml-error' : 'woo-ml-success';
            echo "<div class='$class'><p>{$message['message']}</p></div>";
        }
        ?>
    </div>

    <div class="woo-ml-tab-content" id="settings-tab" role="tabpanel">
        <div class="woo-ml-admin-container">
            <div class="woo-ml-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path d="M12 1l8.217 1.826a1 1 0 0 1 .783.976v9.987a6 6 0 0 1-2.672 4.992L12 23l-6.328-4.219A6 6 0 0 1 3 13.79V3.802a1 1 0 0 1 .783-.976L12 1zm0 2.049L5 4.604v9.185a4 4 0 0 0 1.781 3.328L12 20.597l5.219-3.48A4 4 0 0 0 19 13.79V4.604L12 3.05zM12 7a2 2 0 0 1 1.001 3.732L13 15h-2v-4.268A2 2 0 0 1 12 7z" fill="currentColor" />
                    </svg>
                    Configuración de API
                </h2>
                <p class="woo-ml-description">Ingrese sus credenciales de MercadoLibre para comenzar la sincronización.</p>
                <form method="post" action="" class="woo-ml-form">
                    <?php
                    wp_nonce_field('woo_ml_save_credentials', 'woo_ml_credentials_nonce');
                    $client_id = get_option('woo_ml_client_id');
                    $client_secret = get_option('woo_ml_client_secret');
                    ?>
                    <div class="woo-ml-form-group">
                        <label for="woo_ml_client_id">Client ID</label>
                        <input type="text" id="woo_ml_client_id" name="woo_ml_client_id" value="<?php echo esc_attr($client_id); ?>" class="woo-ml-input" required />
                    </div>
                    <div class="woo-ml-form-group">
                        <label for="woo_ml_client_secret">Client Secret</label>
                        <input type="password" id="woo_ml_client_secret" name="woo_ml_client_secret" value="<?php echo esc_attr($client_secret); ?>" class="woo-ml-input" required />
                    </div>
                    <div class="woo-ml-form-actions">
                        <button type="submit" name="woo_ml_save_credentials" class="woo-ml-btn woo-ml-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path d="M7 19v-6h10v6h2V7.828L16.172 5H5v14h2zM4 3h13l4 4v13a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zm5 12v4h6v-4H9z" fill="currentColor" />
                            </svg>
                            Guardar Credenciales
                        </button>
                        <button type="submit" name="woo_ml_verify_credentials" class="woo-ml-btn woo-ml-btn-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                            </svg>
                            Verificar Credenciales
                        </button>
                    </div>
                </form>
            </div>

            <?php if ($client_id && $client_secret): ?>
                <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13.06 8.11l1.415 1.415a7 7 0 0 1 0 9.9l-.354.353a7 7 0 0 1-9.9-9.9l1.415 1.415a5 5 0 1 0 7.071 7.071l.354-.354a5 5 0 0 0 0-7.07l-1.415-1.415 1.415-1.414zm6.718 6.011l-1.414-1.414a5 5 0 1 0-7.071-7.071l-.354.354a5 5 0 0 0 0 7.07l1.415 1.415-1.415 1.414-1.414-1.414a7 7 0 0 1 0-9.9l.354-.353a7 7 0 0 1 9.9 9.9z" fill="currentColor" />
                        </svg>
                        Conectar con MercadoLibre
                    </h2>
                    <?php
                    $auth_url = $this->get_auth_url();
                    if ($auth_url):
                    ?>
                        <p class="woo-ml-description">Para sincronizar sus productos, necesita conectar su cuenta de MercadoLibre:</p>
                        <a href="<?php echo esc_url($auth_url); ?>" class="woo-ml-btn woo-ml-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path d="M20 12a8 8 0 1 0-3.562 6.657l1.11 1.664A9.953 9.953 0 0 1 12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10v1.5a3.5 3.5 0 0 1-6.396 1.966A5 5 0 1 1 15 8H17v5.5a1.5 1.5 0 0 0 3 0V12zm-8-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6z" fill="currentColor" />
                            </svg>
                            Conectar con MercadoLibre
                        </a>
                    <?php else: ?>
                        <p class="woo-ml-error">Error: No se pudo generar la URL de autorización. Por favor, verifique sus credenciales.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($this->access_token): ?>
                <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Estado de la Conexión
                    </h2>
                    <p class="woo-ml-success"><strong>Conectado a MercadoLibre</strong></p>
                    <div class="woo-ml-btn-group">
                        <form method="post" action="" class="inline-form">
                            <?php wp_nonce_field('ml_logout', 'ml_logout_nonce'); ?>
                            <button type="submit" name="ml_logout" class="woo-ml-btn woo-ml-btn-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                    <path fill="none" d="M0 0h24v24H0z" />
                                    <path d="M5 11h8v2H5v3l-5-4 5-4v3zm-1 7h2.708a8 8 0 1 0 0-12H4A9.985 9.985 0 0 1 12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10a9.985 9.985 0 0 1-8-4z" fill="currentColor" />
                                </svg>
                                Cerrar Sesión
                            </button>
                        </form>
                        <form method="post" action="" class="inline-form">
                            <button type="submit" name="test_ml_connection" class="woo-ml-btn woo-ml-btn-info">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                    <path fill="none" d="M0 0h24v24H0z" />
                                    <path d="M12 3a9 9 0 0 1 9 9h-2a7 7 0 0 0-7-7V3z" fill="currentColor" />
                                </svg>
                                Probar Conexión
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Testing
                    </h2>
                    <p class="woo-ml-description">
                        Este botón está destinado exclusivamente para realizar pruebas. Puedes utilizarlo para testear cualquier función que necesites implementar o verificar su comportamiento dentro del entorno del plugin.
                    </p>
                    <button id="testing" class="woo-ml-btn woo-ml-btn-info" style="background-color: black; color: white;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon" style="fill: white;">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" />
                        </svg>
                        Boton para testear
                    </button>

                    <div id="testing_result" class="woo-ml-result" aria-live="polite"></div>
                </div>
                
                <div class="woo-ml-card">
                  <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" class="woo-ml-icon" style="color: #555;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="11" cy="11" r="8"></circle>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    Herramientas de Búsqueda en Mercado Libre
                  </h2>
                  <p class="woo-ml-description">
                      Utiliza estas herramientas para consultar información de tus productos directamente en Mercado Libre.
                  </p>

                  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                      
                      <div class="woo-ml-card" style="background-color: #f9f9f9;">
                          <h4>Buscar Producto por ID</h4>
                          <div class="woo-ml-form-group">
                              <label for="search-item-id-input">ID de Publicación</label>
                              <input type="text" id="search-item-id-input" class="woo-ml-input" placeholder="Ej: MLC1234567890" />
                          </div>
                          <button id="search-by-id-btn" class="woo-ml-btn woo-ml-btn-primary">Buscar Producto</button>
                      </div>

                      <div class="woo-ml-card" style="background-color: #f9f9f9;">
                          <h4>Buscar Productos por Categoría</h4>
                           <div class="woo-ml-form-group">
                              <label for="search-category-id-input">ID de Categoría</label>
                              <input type="text" id="search-category-id-input" class="woo-ml-input" placeholder="Ej: MLC388722" />
                          </div>
                          <button id="search-by-category-btn" class="woo-ml-btn woo-ml-btn-primary">Buscar en Categoría</button>
                      </div>
                  </div>

                  <div class="woo-ml-card" style="margin-top: 20px;">
                      <div style="display: flex; justify-content: space-between; align-items: center;">
                          <h4>Resultados de la Búsqueda:</h4>
                          <button id="clear-search-log-btn" class="woo-ml-btn" style="background-color: #555; color: white;">Limpiar Log</button>
                      </div>
                      <div id="search-log" style="font-family: monospace; font-size: 13px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; background-color: #fdfdfd; border: 1px solid #eee; padding: 15px; border-radius: 8px; margin-top: 10px;">
                          Esperando una búsqueda...
                      </div>
                  </div>
                </div>
                
                <div class="woo-ml-card">
                    <h2 style="color: red;" >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Herramientas Seller Custom Field
                    </h2>
                    <p class="woo-ml-description">Antes de realizar alguna importación, se recomienda verificar que los productos estén identificados correctamente.</br>Para esto, el producto en MercadoLibre debe tener el <strong>código del producto</strong> almacenado en el campo <strong>SELLER CUSTOM FIELD</strong>.</br><strong>Los resultados se muestran en el Log.</strong></br>Si alguna publicación no tiene código de producto, puede asignarle el <strong>SELLER CUSTOM FIELD</strong> sugerido por el plugin.</p>
                    <select>
                        <option>Seleccione Categoría</option>
                        <option>Pijamas</option>
                    </select>
                    </br></br>
                    
                    <button id="verificar_seller_custom_field" class="woo-ml-btn woo-ml-btn-info" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Verificar seller_custom_field
                    </button>
                    
                    <button id="asignar_seller_custom_field" class="woo-ml-btn woo-ml-btn-info" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Asignar seller_custom_field
                    </button>


                    <div id="seller_custom_field_result" class="woo-ml-result" aria-live="polite"></div>
                </div>
                
                <div class="woo-ml-tab-content" id="importar-tab" role="tabpanel">
                    <div class="woo-ml-admin-container">
                        <div class="woo-ml-card">
                            <h2>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                                    <path fill="none" d="M0 0h24v24H0z"/>
                                    <path d="M13 12h3l-4 4-4-4h3V8h2v4z m-2-8C6.477 4 2 8.477 2 14s4.477 10 10 10 10-4.477 10-10S17.523 4 12 4zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" fill="currentColor"/>
                                </svg>
                                Importar Producto Individualmente
                            </h2>
                            
                            <p class="woo-ml-description">
                                Utiliza esta herramienta para importar o actualizar un único producto desde Mercado Libre hacia WooCommerce.
                            </p>
            
                            <div class="woo-ml-form-group">
                                <label for="ml-product-id-input"><strong>ID de Publicación de Mercado Libre</strong></label>
                                <p style="font-size: 12px; color: #555; margin-top: 0;">
                                    Ingresa el ID de la publicación (ej: MLC1234567890). Este se usará para traer la información del producto.
                                </p>
                                <input type="text" id="ml-product-id-input" class="woo-ml-input" placeholder="Ej: MLC2741894592" style="max-width: 400px;"/>
                            </div>
            
                            <div class="woo-ml-form-actions">
                                <button id="import-single-product-btn" class="woo-ml-btn woo-ml-btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                        <path fill="none" d="M0 0h24v24H0z"/>
                                        <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/>
                                    </svg>
                                    Importar Producto
                                </button>
                                <button id="clear-log-btn" class="woo-ml-btn woo-ml-btn-info" style="background-color: #555;">
                                     <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                        <path fill="none" d="M0 0h24v24H0z"/>
                                        <path d="M17 6h5v2h-2v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8H2V6h5V3a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v3zm-8 5v6h2v-6H9zm4 0v6h2v-6h-2zM9 4v2h6V4H9z" fill="currentColor"/>
                                    </svg>
                                    Limpiar Log
                                </button>
                            </div>
                            
                            <div class="woo-ml-card" style="margin-top: 20px; background-color: #f9f9f9;">
                                <h4>Registro de Importación:</h4>
                                <div id="single-import-log" style="font-family: monospace; font-size: 13px; max-height: 300px; overflow-y: auto; white-space: pre-wrap;">
                                    Esperando una acción...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                 <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Importar Categoria
                    </h2>
                   <p class="woo-ml-description">
                      Esta función permite importar productos filtrando por el ID de la categoría. Utilízala para traer únicamente los productos que pertenezcan a una categoría específica, facilitando así la gestión y sincronización dentro del plugin.
                    </p>


                  <div style="display: flex; flex-direction: column; gap: 10px; max-width: 400px;">
                
                    <!-- Input y botón para buscar por ID -->
                    Ingresa el Id de la categoria que deseas importar aqui!!
                    <input 
                      type="text" 
                      id="woo_ml_categoria_importar" 
                      placeholder="Ej: MLC1234567890" 
                      style="padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px;" 
                    />
                    <button 
                      id="woo_ml_importar_categoria" 
                      class="woo-ml-btn woo-ml-btn-info" 
                      style="width: fit-content; background-color: #007bff; color: white;"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor" />
                        </svg>
                      Importar Categoría
                    </button>



                  </div>

                </div>
                
                <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z"/>
                            <path d="M3 19h18v2H3v-2zm10-5.828L19.071 7.1l-1.414-1.414L13 10.172V2h-2v8.172l-4.657-4.656-1.414 1.414L13 13.172z" fill="currentColor"/>
                        </svg>
                        Importar Productos desde Archivo (por Lotes)
                    </h2>
                    <p class="woo-ml-description">
                      Esta herramienta procesa los IDs del archivo <strong>categoria_id.txt</strong> en lotes de 20. Presiona el botón para procesar el siguiente lote. El proceso continuará hasta que el archivo quede vacío.
                    </p>
                    
                    <button id="importar_lote_btn" class="woo-ml-btn woo-ml-btn-primary">
                        Procesar Primer Lote
                    </button>

                    <div class="woo-ml-card" style="margin-top: 20px; background-color: #f9f9f9;">
                        <h4>Progreso de la Importación:</h4>
                        <div id="importar_progreso" style="font-weight: bold; margin-bottom: 15px;">
                            Esperando para iniciar...
                        </div>
                        <h4>Resultados del último lote:</h4>
                        <div id="importar_resultado" style="font-family: monospace; font-size: 13px; max-height: 300px; overflow-y: auto;">
                            Aquí se mostrarán los resultados...
                        </div>
                    </div>
                </div>
                
                
                <div class="woo-ml-card">
                    <h2 style="color: red;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Herramientas SKU
                    </h2>
                    <p class="woo-ml-description">Antes de realizar alguna importación, se recomienda verificar que los productos estén identificados correctamente.</br>Para esto, el producto en WooCommerce debe tener el <strong>código del producto</strong> almacenado en el campo <strong>SKU</strong>.</br><strong>Los resultados se muestran en el Log.</strong></br>Si alguna publicación no tiene código de producto, puede asignarle el <strong>SKU</strong> sugerido por el plugin.</br><strong>Atención:</strong> La principal causa de fallos a la hora de asignar SKU es que el producto se encuentre duplicado en la tienda. En dicho caso, buscar el producto y eliminar los duplicados.</p>
                    <select>
                        <option>Seleccione Categoría</option>
                        <option>Pijamas</option>
                    </select>
                    </br></br>
                    
                    <button id="verificar_sku" class="woo-ml-btn woo-ml-btn-info" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Verificar SKU
                    </button>
                    
                    <button id="asignar_sku" class="woo-ml-btn woo-ml-btn-info" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Asignar SKU
                    </button>

                    
                    <div id="sku_result" class="woo-ml-result" aria-live="polite"></div>
                </div>

                <div class="woo-ml-card">
                    <h2 style="color: red;" >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Importaciones por Categoría
                    </h2>

                    <p class="woo-ml-description">Importa todos los productos activos de una plataforma a otra, seleccionando una categoría.</p>
                    <select>
                        <option>Seleccione Categoría</option>
                        <option>Pijamas</option>
                    </select>
                    </br></br>
                    
                    <button id="sync-wc" class="woo-ml-btn woo-ml-btn-primary" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor" />
                        </svg>
                        Importar desde WooCommerce a MercadoLibre
                    </button>
                    
                    <button id="sync-pijamas" class="woo-ml-btn woo-ml-btn-danger" style="background: linear-gradient(to right, #d7d74a, #7f54b3); color: black;" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor" />
                        </svg>
                        Importar desde MercadoLibre a WooCommerce
                    </button>

                    
                    <div id="importacion-categoria-result" class="woo-ml-result" aria-live="polite"></div>
                </div>

                <div class="woo-ml-card">
                    <h2 style="color: red;" >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13 21v2h-2v-2H3a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h18a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-8zm-9-2h16V5H4v14zm9-1a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0-6a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill="currentColor" />
                        </svg>
                        Importación masiva
                    </h2>
                    <p class="woo-ml-description">Importa todos los productos de una plataforma a otra</p>
                    
                    <button class="woo-ml-btn woo-ml-btn-primary" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor" />
                        </svg>
                        Importación Masiva de WooCommerce a MercadoLibre
                    </button>
                    
                    <button id="sync-ml" class="woo-ml-btn woo-ml-btn-danger" style="background: linear-gradient(to right, #d7d74a, #7f54b3); color: black;" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor" />
                        </svg>
                        Importación Masiva de MercadoLibre a WooCommerce
                    </button>

                    <div id="sync-result" class="woo-ml-result" aria-live="polite"></div>
                    </br>
                    <div id="download-log-container" style="display: none;">
                        <button id="descargarinformesku" class="woo-ml-btn woo-ml-btn-info">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                                <path fill="none" d="M0 0h24v24H0z" />
                                <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                            </svg>
                            <a
                                href="<?php echo esc_url(site_url('/wp-content/productos-sin-sku.log')); ?>"
                                download="informesku"
                                style="text-decoration: none; color: #fff; transition: color 0.3s, background-color 0.3s;"
                                onmouseover="this.style.color='#fff'; this.style.backgroundColor='#007bff'; this.style.padding='2px 5px'; this.style.borderRadius='3px';"
                                onmouseout="this.style.color='#fff'; this.style.backgroundColor='transparent'; this.style.padding='0'; this.style.borderRadius='0';">
                                Descargar Informe
                            </a>
                        </button>
                    </div>
                </div>

                <div class="woo-ml-card">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M22.7 19.3l-4.6-4.6c.4-1.1.6-2.3.6-3.5 0-5-4-9-9-9-1.2 0-2.4.2-3.5.6l4.6 4.6-4.2 4.2-4.6-4.6c-.4 1.1-.6 2.3-.6 3.5 0 5 4 9 9 9 1.2 0 2.4-.2 3.5-.6l4.6 4.6c.4.4 1 .4 1.4 0l2.1-2.1c.4-.4.4-1 0-1.4z" fill="currentColor" />
                        </svg>
                        Herramientas
                    </h2>
                    <p class="woo-ml-description">Borrar todos los productos de:</p>
                    <button id="botonBorrar-wc" class="woo-ml-btn woo-ml-btn-danger" style="background-color: #7f54b3; color: black; margin-right: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M18.3 5.71L12 12l6.3 6.29-1.42 1.42L12 13.41l-6.29 6.3-1.42-1.42L10.59 12 4.29 5.71 5.71 4.29 12 10.59l6.29-6.3z" fill="currentColor" />
                        </svg>
                        Eliminar todos los productos de WooCommerce
                    </button>
                    </br> </br>
                    <button id="prepare_product_test" class="woo-ml-btn woo-ml-btn-info">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Agregar producto a Mercadolibre
                    </button>
                    </br> </br>
                    <button id="verificar-sku" class="woo-ml-btn woo-ml-btn-info">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-.997-4L6.76 11.757l1.414-1.414 2.829 2.829 5.656-5.657 1.415 1.414L11.003 16z" fill="currentColor" />
                        </svg>
                        Botón de Pruebas
                    </button>
                    <div id="sync-result-borrar" class="woo-ml-result" aria-live="polite"></div>
                </div>
            <?php endif; ?>

            <div class="woo-ml-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z" fill="currentColor" />
                    </svg>
                    Registro de Depuración
                </h2>
                <p class="woo-ml-description">Aquí puede ver los registros de depuración para solucionar problemas:</p>
                <textarea readonly class="woo-ml-debug-log" aria-label="Registro de depuración">
<?php
foreach ($this->debug_messages as $message) {
    echo esc_html($message) . "\n";
}
?>
</textarea>
            </div>
        </div>
    </div>

    <div class="woo-ml-tab-content" id="products-tab" style="display: none;" role="tabpanel">
        <div class="woo-ml-admin-container">
            <div class="woo-ml-card">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path d="M3 10h18v10h-18v-10zm0-2v-5h18v5h-18zm2-1h14v-2h-14v2zm10 10h2v-6h-2v6zm-4 0h2v-6h-2v6zm-4 0h2v-6h-2v6zm-4 0h2v-6h-2v6z" fill="currentColor" />
                    </svg>
                    Productos Sincronizados
                </h2>
                <div id="total-items-container" class="total-items-container">
                    <div class="total-item-box total-ml-container">Productos de MercadoLibre: <span id="total-ml-items">0</span></div>
                    <div class="total-item-box total-wc-container">Productos de WooCommerce: <span id="total-wc-items">0</span></div>
                </div>
                <div id="Importacion" style="display: none;">
                    <p>Atención: Existen más productos en Mercadolibre que en WooCommerce. Debe sincronizar nuevamente.</p>
                </div>
                <br>
                <div class="woo-ml-products-filters">
                    <select id="sync-status-filter" class="woo-ml-input">
                        <option value="">Todos</option>
                        <option value="synced">Sincronizados</option>
                        <option value="not-synced">No sincronizados</option>
                    </select>
                    <input type="text" id="product-search" placeholder="Buscar productos..." class="woo-ml-input" aria-label="Buscar productos" />
                    <button id="search-button" class="woo-ml-button woo-ml-button-custom">Buscar</button>
                </div>
                <div class="woo-ml-table-responsive">
                    <table class="woo-ml-table" aria-label="Lista de productos sincronizados">
                        <thead>
                            <tr>
                                <th scope="col">Imagen</th>
                                <th scope="col">ID</th>
                                <th scope="col">Producto</th>
                                <th scope="col">SKU</th>
                                <th scope="col">MercadoLibre</th>
                                <th scope="col">WooCommerce</th>
                                <th scope="col">Tienda</th>
                            </tr>
                        </thead>
                        <tbody id="products-list">
                            <!-- Products will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="woo-ml-pagination" role="navigation" aria-label="Paginación de productos">
                    <button class="woo-ml-btn woo-ml-btn-secondary" id="load-prev-page" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M10.828 12l4.95 4.95-1.414 1.414L8 12l6.364-6.364 1.414 1.414z" fill="currentColor" />
                        </svg>
                        Anterior
                    </button>
                    <span id="page-info">Página 1</span>
                    <button class="woo-ml-btn woo-ml-btn-secondary" id="load-next-page">
                        Siguiente
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
                            <path fill="none" d="M0 0h24v24H0z" />
                            <path d="M13.172 12l-4.95-4.95 1.414-1.414L16 12l-6.364 6.364-1.414-1.414z" fill="currentColor" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="woo-ml-tab-content" id="auditoria-tab" style="display: none;" role="tabpanel">
    <div class="woo-ml-admin-container">
        <div class="woo-ml-card">
            <h2>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" class="woo-ml-icon">
                    <path fill="none" d="M0 0h24v24H0z" />
                    <path d="M3 4v16h18V4H3zm2 2h14v12H5V6zm2 2v2h10V8H7zm0 4v2h7v-2H7z" fill="currentColor" />
                </svg>
                Auditoría de Productos ML
            </h2>

            <?php include plugin_dir_path(__FILE__) . 'ml_auditor/view_auditor.php'; ?>

        </div>
    </div>
</div>

</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');

    :root {
        --primary-color: #3498db;
        --secondary-color: #2ecc71;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --info-color: #34495e;
        --light-gray: #f5f7fa;
        --dark-gray: #2c3e50;
        --border-color: #e0e0e0;
        --text-color: #333333;
        --background-color: #ffffff;
    }
    
    

    .woo-ml-admin {
        font-family: 'Inter', sans-serif;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        color: var(--text-color);
        background-color: var(--background-color);
    }

    .woo-ml-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .woo-ml-header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .woo-ml-icon {
        margin-right: 12px;
        vertical-align: middle;
    }

    .woo-ml-tabs {
        display: flex;
        background-color: var(--light-gray);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .woo-ml-tab {
        padding: 15px 25px;
        color: var(--text-color);
        text-decoration: none;
        font-weight: 500;
        transition: background-color 0.3s, color 0.3s;
        display: flex;
        align-items: center;
    }

    .woo-ml-tab:hover,
    .woo-ml-tab.active {
        background-color: var(--primary-color);
        color: white;
    }

    .woo-ml-card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 25px;
        margin-bottom: 30px;
        transition: box-shadow 0.3s ease;
    }

    .woo-ml-card:hover {
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .woo-ml-card h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 20px;
        color: var(--primary-color);
        display: flex;
        align-items: center;
    }

    .woo-ml-description {
        color: var(--dark-gray);
        margin-bottom: 20px;
    }

    .woo-ml-form-group {
        margin-bottom: 25px;
    }

    .woo-ml-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark-gray);
    }

    .woo-ml-input,
    .woo-ml-select {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    .woo-ml-input:focus,
    .woo-ml-select:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
    }

    .woo-ml-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.1s;
    }

    .woo-ml-btn:hover {
        transform: translateY(-1px);
    }

    .woo-ml-btn:active {
        transform: translateY(1px);
    }

    .woo-ml-btn-primary {
        background-color: var(--primary-color);
        color: white;
    }

    .woo-ml-btn-primary:hover {
        background-color: #2980b9;
        /* Ajusta el color al hacer hover */
    }

    .woo-ml-btn-secondary {
        background-color: var(--secondary-color);
        color: white;
    }

    .woo-ml-btn-secondary:hover {
        background-color: #27ae60;
        /* Ajusta el color al hacer hover */
    }

    .woo-ml-btn-danger {
        background-color: var(--danger-color);
        color: white;
    }

    .woo-ml-btn-danger:hover {
        background-color: #c0392b;
        /* Ajusta el color al hacer hover */
    }

    .woo-ml-btn-info {
        background-color: var(--info-color);
        color: white;
    }

    .woo-ml-btn-info:hover {
        background-color: #2c3e50;
        /* Ajusta el color al hacer hover */
    }

    .woo-ml-btn-group {
        display: flex;
        gap: 12px;
    }

    .woo-ml-success {
        background-color: #d4edda;
        color: #155724;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .woo-ml-error {
        background-color: #f8d7da;
        color: #721c24;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .woo-ml-debug-log {
        width: 100%;
        height: 200px;
        font-family: 'Roboto Mono', monospace;
        background-color: var(--light-gray);
        border: 1px solid var(--border-color);
        padding: 12px;
        border-radius: 8px;
        font-size: 12px;
        resize: vertical;
    }

    .woo-ml-products-filters {
        display: flex;
        gap: 12px;
        margin-bottom: 25px;
    }

    .woo-ml-table-responsive {
        overflow-x: auto;
    }

    .woo-ml-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .woo-ml-table th,
    .woo-ml-table td {
        padding: 14px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }

    .woo-ml-table th {
        background-color: var(--light-gray);
        font-weight: 600;
        color: var(--dark-gray);
    }

    .woo-ml-table tr:hover {
        background-color: var(--light-gray);
    }

    .product-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
    }

    .sync-status {
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }

    .sync-status.synced {
        background-color: var(--success-color);
        color: white;
    }

    .sync-status.not-synced {
        background-color: var(--danger-color);
        color: white;
    }

    .sync-status.error {
        background-color: var(--danger-color);
        color: white;
    }

    .total-items-container {
        display: flex;
        gap: 20px;
    }

    .total-item-box {
        width: 210px;
        height: 20px;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        background-color: var(--default-color);
    }

    .total-ml-container {
        background-color: #d7d74a;
    }

    .total-wc-container {
        background-color: #7f54b3;
        color: white;
    }

    .btn {
        display: inline-block;
        padding: 8px 12px;
        margin: 4px 2px;
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        text-align: center;
        text-decoration: none;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    .btn-ml {
        background-color: rgb(221, 221, 53);
        color: #000;
    }

    .btn-ml:hover {
        background-color: rgb(199, 199, 146);
        color: #000;
    }

    .btn-wc {
        background-color: #7f54b3;
    }

    .btn-wc:hover {
        background-color: rgb(212, 140, 212);
    }

    .btn-page {
        background-color: rgb(45, 24, 54);
    }

    .btn-page:hover {
        background-color: rgb(93, 56, 109);
    }

    .woo-ml-button-custom {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .woo-ml-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-top: 25px;
    }

    #page-info {
        font-weight: 500;
        color: var(--dark-gray);
    }
    
    #import-log {
    max-height: 400px;
    overflow-y: auto;
    background-color: #1e1e1e;
    color: #eee;
    font-family: 'Roboto Mono', monospace;
    font-size: 13px;
    border: 1px solid #333;
    border-radius: 6px;
    padding: 15px;
    margin-top: 15px;
    white-space: pre-wrap;
}

.import-log-entry {
    margin-bottom: 12px;
    padding: 10px 15px;
    border-left: 4px solid #0073aa;
    border-radius: 4px;
    background-color: #2c2c2c;
    line-height: 1.4;
    overflow-wrap: break-word;
}

.import-log-entry.loading {
    border-left-color: #f1c40f;
    background-color: #2e2a15;
}

.import-log-entry.success {
    border-left-color: #2ecc71;
    background-color: #1e2f1e;
}

.import-log-entry.error {
    border-left-color: #e74c3c;
    background-color: #311f1f;
}

#import-log pre {
    background: #111;
    border: 1px solid #444;
    padding: 10px;
    margin-top: 8px;
    border-radius: 4px;
    white-space: pre-wrap;
    color: #dcdcdc;
    font-size: 12px;
}


    @media (max-width: 768px) {
        .woo-ml-tabs {
            flex-direction: column;
        }

        .woo-ml-products-filters {
            flex-direction: column;
        }

        .woo-ml-btn-group {
            flex-direction: column;
        }

        .woo-ml-table th,
        .woo-ml-table td {
            padding: 10px;
        }
        

    }
</style>
<script>
    jQuery(document).ready(function($) {
        const verificarSkuObj = {
            scrollId: null,
            hasMore: null,
            sinSKu: [],
        };
        $('#buscarcategoria').on('click', function() {
            var button = $(this);
            var resultDiv = $('#buscarcategoria-result');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'find_best_category',
                    nonce: '<?php echo wp_create_nonce("find_best_category_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<p>La mejor categoria es: <strong>' + response.data.category + '</strong></p>');
                    } else {
                        resultDiv.text('Error al buscar la categoria');
                    }
                    button.prop('disabled', false);
                    button.html('Buscar Categoria');
                },
            });
        });
        // Tab switching
        $('.woo-ml-tab').on('click', function(e) {
            e.preventDefault();
            const tabId = $(this).data('tab');

            $('.woo-ml-tab').removeClass('active').attr('aria-selected', 'false');
            $(this).addClass('active').attr('aria-selected', 'true');

            $('.woo-ml-tab-content').hide().attr('aria-hidden', 'true');
            $(`#${tabId}-tab`).show().attr('aria-hidden', 'false');

            if (tabId === 'products') {
                loadProducts(1);
            }
        });

        // Sincronizar Mercadolibre a WooCommerce
        $('#herramienta_multiuso').on('click', function() {
            verificadorSku();
        });

        function verificadorSku() {
            const {
                scrollId,
                hasMore,
                sinSKu
            } = verificarSkuObj;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'herramienta_multiuso',
                    scroll_id: scrollId,
                    has_more: hasMore,
                    productosSinSku: JSON.stringify(sinSKu),
                },
                success: function(response) {
                    console.info("Verificar sku: ", response);
                    // Actualizar datos de verificación
                    verificarSkuObj.scrollId = response.data.scroll_id;
                    verificarSkuObj.sinSku = response.data.productosSinSku;

                    //Si quedan datos por verificar, se vuelve a llamar a la función
                    if (response.data.has_more) {
                        verificadorSku();
                    }
                },
                error: function() {
                    alert("Error");
                }
            })
        };

        // Products pagination and filtering
        let currentPage = 1;
        let totalPages = 1;

        function loadProducts(page) {
            const statusFilter = $('#sync-status-filter').val();
            const searchQuery = $('#product-search').val();
            const tableBody = $('#products-list');

            tableBody.html('<tr><td colspan="8" class="text-center">Cargando...</td></tr>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_synced_products',
                    nonce: '<?php echo wp_create_nonce("get_synced_products_nonce"); ?>',
                    page: page,
                    status: statusFilter,
                    search: searchQuery
                },
                success: function(response) {
                    if (response.success) {
                        const products = response.data.products;
                        totalPages = response.data.total_pages;
                        currentPage = page;

                        $('#load-prev-page').prop('disabled', page <= 1);
                        $('#load-next-page').prop('disabled', page >= totalPages);
                        $('#page-info').text(`Página ${page} de ${totalPages}`);

                        tableBody.empty();

                        if (products.length === 0) {
                            tableBody.html('<tr><td colspan="8" class="text-center">No se encontraron productos</td></tr>');
                            return;
                        }

                        products.forEach(function(product) {
                            const row = `
<tr>
<td><img src="${product.image}" alt="${product.name}" class="product-image" /></td>
<td>${product.id}</td>
<td>${product.name}</td>
<td>${product.sku}</td>
<td><a href="${product.permalink_ml}" target="_blank" class="btn btn-ml">Ver en MercadoLibre</a></td>
<td><a href="${product.permalink_wc}" target="_blank" class="btn btn-wc">Ver en WooCommerce</a></td>
<td><a href="${product.permalink_page}" target="_blank" class="btn btn-page">Ver en Tienda</a></td>
</tr>
`;
                            tableBody.append(row);
                        });
                        $('#total-items').text(response.data.total_items);
                        $('#total-ml-items').text(response.data.total_ml_items);
                        $('#total-wc-items').text(response.data.total_wc_items);
                        if (response.data.total_ml_items > response.data.total_wc_items) {
                            $('#Importacion').show();
                        }
                    }
                },
                error: function() {
                    tableBody.html('<tr><td colspan="8" class="text-center">Error al cargar los productos</td></tr>');
                }
            });
        }

        function getStatusLabel(status) {
            const labels = {
                'synced': 'Sincronizado',
                'not-synced': 'No sincronizado',
                'error': 'Error'
            };
            return labels[status] || status;
        }



        // Pagination handlers
        $('#load-prev-page').on('click', function() {
            if (currentPage > 1) {
                loadProducts(currentPage - 1);
            }
        });

        $('#load-next-page').on('click', function() {
            if (currentPage < totalPages) {
                loadProducts(currentPage + 1);
            }
        });

        // Filter handlers
        $('#sync-status-filter').on('change', function() {
            loadProducts(1);
        });

        $('#search-button').on('click', function() {
            loadProducts(1); // Llama a la función de carga de productos al hacer clic en el botón
        });

        //Borrar todos los productos de WooCommerce
        $('#botonBorrar-wc').on('click', function() {
            if (!confirm("¿Estás seguro de que deseas eliminar todos tus productos de WooCommerce? Esta acción es irreversible.")) {
                return;
            }
            var button = $(this);
            var resultDiv = $('#sync-result-borrar');
            button.prop('disabled', true);
            button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Borrando...');
            resultDiv.text('Borrando productos...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'borrarProductosWooCommerce',
                    nonce: '<?php echo wp_create_nonce("borrarProductosWooCommerce_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<p class="woo-ml-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    } else {
                        resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    }
                },
                error: function() {
                    resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> Error en la eliminación. Por favor, intente nuevamente.</p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/></svg> Eliminar todos los productos de WooCommerce');
                }
            });
        });

        //Agregar producto a Mercadolibre
        $('#prepare_product_test').on('click', function() {
            var button = $(this);
            var resultDiv = $('#sync-result-borrar');
            button.prop('disabled', true);
            button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Agregando...');
            resultDiv.text('Agregando producto a Mercadolibre...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'upload_product_to_mercadolibre'
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<p class="woo-ml-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    } else {
                        resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    }
                },
                error: function() {
                    resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> Error al buscar SKU. Por favor, intente nuevamente.</p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/></svg> Agregar producto a Mercadolibre');
                }
            });
        });




        // Método para borrar todos los productos de Mercadolibre
        $('#botonBorrar-ml').on('click', function() {
            alert("Boton desactivado por seguridad");
            return;
            var button = $(this);
            var resultDiv = $('#sync-result-borrar');
            button.prop('disabled', true);
            button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Borrando...');
            resultDiv.text('Borrando productos...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'borrarProductosMercadolibre',
                    nonce: '<?php echo wp_create_nonce("borrarProductosMercadolibre_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<p class="woo-ml-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    } else {
                        resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> ' + response.data + '</p>');
                    }
                },
                error: function() {
                    resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg> Error en la eliminación. Por favor, intente nuevamente.</p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/></svg> Eliminar todos los productos de Mercadolibre');
                }
            });
        });

        // manual product sync handler
        $('#sync-manual-product').on('click', function() {
            const productId = $('#manual_product_id').val();
            var resultDiv = $('#sync-result-manual');
            if (!productId) {
                resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg>Por favor, ingrese una ID de producto válida.</p>');
                return;
            }

            const button = $(this);
            button.prop('disabled', true);
            button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Sincronizando...');

            console.log(productId);
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'importarProductoDesdeMercadolibre',
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        resultDiv.html('<p class="woo-ml-success"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/></svg> ' + 'Producto sincronizado exitosamente.' + '</p>');
                    } else {
                        resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg>' + 'Error al sincronizar: ' + response.data + '</p>');
                    }
                },
                error: function() {
                    resultDiv.html('<p class="woo-ml-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/></svg>' + 'Error de conexión al sincronizar el producto' + '</p>');
                },
                complete: function() {
                    button.prop('disabled', false);
                    button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/></svg> Sincronizar Producto');
                }
            });
        });
    });
</script>
<script>
    jQuery(document).ready(function($) {

        const syncMlObj = {
            button: null,
            resultDiv: $('#sync-result'),
            scrollId: null,
            totalSynced: 0,
            totalFailed: 0,
            skipped: 0,
            offset: 0,
            initialOffset: 0,
            retryIds: [],
            finalIds: [],
            trackFail: 0,
            retryFailLimit: 3,
            state: 'importing',
        };

        // Sincronizar Mercadolibre a WooCommerce
        $('#sync-ml').on('click', function() {
            syncMlObj.button = $(this);
            startSync();
        });

        function startSync() {
            const {
                button,
                resultDiv
            } = syncMlObj;

            button.prop('disabled', true);
            button.html(
                `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
<path fill="none" d="M0 0h24v24H0z"/>
<path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor">
<animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
</path>
</svg> Sincronizando...`
            );
            resultDiv.text('Sincronización en progreso...');

            syncBatch();
        }

        function syncBatch() {
            const {
                scrollId,
                offset,
                initialOffset,
                retryIds,
                state,
                button,
                resultDiv
            } = syncMlObj;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'importarTodoMercadolibre',
                    nonce: '<?php echo wp_create_nonce("importarTodoMercadolibre_nonce"); ?>',
                    scroll_id: scrollId,
                    offset: offset,
                    initial_offset: initialOffset,
                    retry_ids: JSON.stringify(retryIds),
                    state: 'importing',
                },
                success: function(response) {
                    //console.info(response.data);

                    if (response.success) {
                        // Update syncMlObj state
                        syncMlObj.totalSynced += response.data.synced;
                        syncMlObj.totalFailed += response.data.failed;
                        syncMlObj.scrollId = response.data.scroll_id;
                        syncMlObj.offset = response.data.offset;
                        syncMlObj.skipped += response.data.skipped;
                        syncMlObj.retryIds = response.data.retry_ids;
                        syncMlObj.state = response.data.state;
                        let timeLeft = response.data.time_left;
                        let leftProducts = response.data.left_products;

                        if (response.data.has_more) {
                            resultDiv.html(`
<p class="woo-ml-success">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
<path fill="none" d="M0 0h24v24H0z"/>
<path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/>
</svg>
[Tiempo Restante: ${timeLeft}] Sincronización en proceso... Sincronizados hasta el momento: ${syncMlObj.totalSynced}, Fallidos: ${syncMlObj.totalFailed}, Skip: ${syncMlObj.skipped}, Faltan: ${leftProducts}
</p>
`);
                            syncBatch();
                        } else {
                            handleCompletion(response);
                        }
                    }
                },
                error: function(error, textStatus, errorThrown) {
                    const status = error.status || 0; // HTTP status code, default to 0 if undefined
                    const statusText = error.statusText || 'error'; // HTTP status text
                    const responseText = error.responseText || 'No response from server'; // Response body
                    const readyState = error.readyState || 'unknown'; // Ready state of the request

                    let parsedResponse = null;
                    try {
                        parsedResponse = JSON.parse(responseText); // Try parsing the response if it's JSON
                    } catch (e) {
                        parsedResponse = responseText; // Keep as raw text if parsing fails
                    }

                    // Log detailed error information
                    console.error('AJAX Error Details:', {
                        status,
                        statusText,
                        errorThrown,
                        readyState,
                        response: parsedResponse,
                    });

                    // Display a meaningful error message
                    resultDiv.html(`
<p class="woo-ml-error">
Hubo un error al realizar la solicitud.<br>
<strong>Código:</strong> ${status} - ${statusText}<br>
<strong>Detalles:</strong> ${parsedResponse || responseText}<br>
<strong>Error Interno:</strong> ${errorThrown || 'Desconocido'}<br>
<strong>Estado de la Solicitud:</strong> ${readyState}
</p>
`);

                    resetButton('Sincronizar de MercadoLibre a WooCommerce');
                }
            });
        }

        function retrySync() {
            const {
                retryIds,
                button,
                resultDiv,
                finalIds
            } = syncMlObj;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'importarTodoMercadolibre',
                    nonce: '<?php echo wp_create_nonce("importarTodoMercadolibre_nonce"); ?>',
                    retry_ids: JSON.stringify(retryIds),
                    final_failed_ids: JSON.stringify(finalIds),
                    state: 'retrying',
                },
                success: function(response) {
                    //console.info(response.data);

                    if (response.success) {
                        // Update syncMlObj state
                        syncMlObj.totalFailed -= response.data.synced;
                        syncMlObj.retryIds = response.data.retry_ids;
                        syncMlObj.finalIds = response.data.final_failed_ids;

                        if (response.data.has_more) {
                            resultDiv.html(`
`);
                            resultDiv.html(`
<p class="woo-ml-success">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
<path fill="none" d="M0 0h24v24H0z"/>
<path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z" fill="currentColor"/>
</svg>
Resincronización en proceso... Sincronizados hasta el momento: ${syncMlObj.totalSynced}, Fallidos: ${syncMlObj.totalFailed}, Skip: ${syncMlObj.skipped}
</p>
`);
                            retrySync();
                        } else {
                            handleCompletion(response);
                        }
                    }
                },
                error: function() {
                    resultDiv.html('<p class="woo-ml-error">Hubo un error al reintentar los productos fallidos. Intenta nuevamente.</p>');
                    resetButton('Reintentar productos fallidos');
                }
            });
        }

        function renderList(listObj) {
            const {
                resultDiv
            } = syncMlObj;

            const failedList = Object.keys(listObj).map(id => `<li>${id}</li>`).join('');
            resultDiv.html(`
<p class="woo-ml-error">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
<path fill="none" d="M0 0h24v24H0z"/>
<path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-7v2h2v-2h-2zm0-8v6h2V7h-2z" fill="currentColor"/>
</svg>
Sincronización completa con errores. Sincronizados: ${syncMlObj.totalSynced}, Fallidos: ${syncMlObj.totalFailed}.<br/>
Productos fallidos:<ul>${failedList}</ul>
</p>
`);
            $('#download-log-container').show();

        }

        function handleCompletion(response) {
            const {
                button,
                resultDiv
            } = syncMlObj;

            if (response.data.state === 'completed_with_errors') {
                renderList(syncMlObj.retryIds);
                resetSyncMlObjCounters();
                // Update button and rebind click handler
                button.prop('disabled', false)
                    .html('Reintentar productos fallidos')
                    .off('click') // Remove any previous click handlers
                    .on('click', function() {
                        retrySync();
                        button.prop('disabled', true)
                        button.html(
                            `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon">
<path fill="none" d="M0 0h24v24H0z"/>
<path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor">
<animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
</path>
</svg> Reintentando...
`);
                        resultDiv.text('Reintentando sincronización...');
                    });

            } else {
                resultDiv.html(`
<p class="woo-ml-success">
Sincronización completa. Sincronizados: ${syncMlObj.totalSynced}, Fallidos: ${syncMlObj.totalFailed}
</p>
`);

                if (Object.keys(syncMlObj.finalIds).length > 0) {
                    renderList(syncMlObj.finalIds);
                }

                resetSyncMlObj();
                resetButton('Sincronizar nuevamente');
            }
        }

        function resetButton(label) {
            const {
                button
            } = syncMlObj;
            button.prop('disabled', false).html(label);
        }

        function resetSyncMlObjCounters() {
            syncMlObj.totalSynced = 0;
            syncMlObj.totalFailed = 0;
            syncMlObj.skipped = 0;
            syncMlObj.offset = 0;
            syncMlObj.initialOffset = 0;
        }

        function resetSyncMlObj() {
            syncMlObj.scrollId = null;
            syncMlObj.totalSynced = 0;
            syncMlObj.totalFailed = 0;
            syncMlObj.skipped = 0;
            syncMlObj.offset = 0;
            syncMlObj.initialOffset = 0;
            syncMlObj.retryIds = [];
            syncMlObj.finalIds = [];
            syncMlObj.state = 'importing';
        }

    });
</script>

<script>
    jQuery(document).ready(function($) {
        const crearSkuObj = {
            button: null, // Button reference will be set dynamically
            resultDiv: null, // Result div reference will be set dynamically
            scrollId: null, // Scroll ID for API pagination
            successCount: 0, // Number of successful SKUs created
            errorCount: 0, // Number of errors encountered
            retryIds: {}, // Products that failed and need retries (with retry attempts)
            retryLimit: 10, // Max retries per product
            state: 'creating', // Current state
            finalIds: [], // Store final IDs (success, failed, or retrying)
        };

        function resetCrearSkuObj() {
            crearSkuObj.scrollId = null;
            crearSkuObj.successCount = 0;
            crearSkuObj.errorCount = 0;
            crearSkuObj.retryIds = {};
            crearSkuObj.finalIds = []; // Reset final IDs
            crearSkuObj.retryLimit = 10;
            crearSkuObj.state = 'creating';
        }

        $('#crearSku').on('click', function() {
            crearSkuObj.button = $(this); // Set button reference
            crearSkuObj.resultDiv = $('#crear-sku-result'); // Set result div reference

            // Disable button while processing
            crearSkuObj.button.prop('disabled', true).html('Procesando SKU...');

            function procesarProductos() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'crearSku',
                        nonce: '<?php echo wp_create_nonce("crearSku_nonce"); ?>',
                        scroll_id: crearSkuObj.scrollId,
                        retry_ids: JSON.stringify(crearSkuObj.retryIds),
                        state: crearSkuObj.state,
                    },
                    success: function(response) {
                        console.info(response, crearSkuObj);
                        if (response.success) {
                            crearSkuObj.scrollId = response.data.scroll_id;
                            crearSkuObj.successCount += response.data.synced || 0;
                            crearSkuObj.errorCount += response.data.failed || 0;
                            crearSkuObj.retryIds = response.data.retry_ids; // Update retry IDs
                            crearSkuObj.state = response.data.state;

                            crearSkuObj.resultDiv.html(`
<p>Estado: ${crearSkuObj.state}</p>
<p>Productos procesados: ${
crearSkuObj.successCount + crearSkuObj.errorCount
}</p>
<p>Éxitos: ${crearSkuObj.successCount} | Errores: ${crearSkuObj.errorCount}</p>
`);

                            if (response.data.has_more) {
                                procesarProductos();
                            } else {
                                manejarReintentos();
                            }
                        } else {
                            crearSkuObj.resultDiv.html(
                                `<p class="woo-ml-error">${response.data}</p>`
                            );
                            finalizarProceso();
                        }
                    },
                    error: function() {
                        crearSkuObj.resultDiv.html(
                            '<p class="woo-ml-error">Error crítico. Por favor, intente nuevamente.</p>'
                        );
                        finalizarProceso();
                    },
                });
            }

            function manejarReintentos() {
                if (crearSkuObj.state == 'completed' || crearSkuObj.state == 'completed_with_retries') {
                    finalizarProceso();
                }

                if (Object.keys(crearSkuObj.retryIds).length > 0) {
                    crearSkuObj.resultDiv.html('<p>Reintentando productos fallidos...</p>');
                    retrySku();
                } else {
                    finalizarProceso();
                }

            }

            function retrySku() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'crearSku',
                        nonce: '<?php echo wp_create_nonce("crearSku_nonce"); ?>',
                        retry_ids: JSON.stringify(crearSkuObj.retryIds),
                        state: crearSkuObj.state,
                    },
                    success: function(response) {
                        console.info(response, crearSkuObj);
                        if (response.success) {
                            crearSkuObj.successCount += response.data.synced || 0;
                            crearSkuObj.errorCount += response.data.failed || 0;
                            crearSkuObj.retryIds = response.data.retry_ids;
                            crearSkuObj.state = response.data.state;

                            crearSkuObj.resultDiv.html(`
<p>Reintentos completados. Éxitos: ${crearSkuObj.successCount} | Errores: ${crearSkuObj.errorCount}</p>
`);

                            if (Object.keys(crearSkuObj.retryIds).length > 0 && response.data.state !== 'completed' || response.data.state !== 'completed_with_retries') {
                                manejarReintentos(); // Only retry if there are still products to retry
                            } else {
                                mostrarResultadoFinal();
                            }
                        } else {
                            crearSkuObj.resultDiv.html(
                                `<p class="woo-ml-error">${response.data}</p>`
                            );
                            finalizarProceso();
                        }
                    },
                    error: function() {
                        crearSkuObj.resultDiv.html(
                            '<p class="woo-ml-error">Error al intentar nuevamente los productos fallidos.</p>'
                        );
                        finalizarProceso();
                    },
                });
            }

            function mostrarResultadoFinal() {
                // Show the final list of products (success, failed, or retrying)
                let finalTable = '<table border="1"><thead><tr><th>ID Producto</th><th>Estado</th></tr></thead><tbody>';

                // Success products
                for (let id in crearSkuObj.retryIds) {
                    if (crearSkuObj.retryIds[id] >= crearSkuObj.retryLimit) {
                        crearSkuObj.finalIds.push({
                            id: id,
                            state: 'Fallido (Exceso de intentos)'
                        });
                    } else {
                        crearSkuObj.finalIds.push({
                            id: id,
                            state: 'Reintentando'
                        });
                    }
                }

                // Add final product data to the table
                crearSkuObj.finalIds.forEach(function(product) {
                    finalTable += `<tr><td>${product.id}</td><td>${product.state}</td></tr>`;
                });

                finalTable += '</tbody></table>';
                crearSkuObj.resultDiv.html(finalTable);
                finalizarProceso();
            }

            function finalizarProceso() {
                resetCrearSkuObj();
                crearSkuObj.button.prop('disabled', false).html('Crear SKU');
            }

            procesarProductos();
        });
    });
</script>

<script>
    jQuery(document).ready(function($) {
        const pijamas_objeto = {
            status: "capturando_ids",
            offset: 0,
            importados: 0,
            pendientes: [],
            total: 0,
            resultDiv: $('#importacion-categoria-result'),
            reintentos: 0,
            maxReintentos: 5,
            baseDelay: 1000 // Base de retraso para reintentos
        };

        $('#sync-pijamas').on('click', function() {
            finalizarProceso(); // Reinicia los valores al hacer clic

            function import_pijamas() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'importar_pijamas',
                        status: pijamas_objeto.status,
                        offset: pijamas_objeto.offset,
                        importados: pijamas_objeto.importados,
                        pendientes: JSON.stringify(pijamas_objeto.pendientes),
                        total: pijamas_objeto.total,
                    },
                    success: function(response) {
                        if (!response || !response.data) {
                            manejarError("Respuesta inválida del servidor");
                            return;
                        }

                        // Actualiza el estado y los valores del objeto
                        pijamas_objeto.status = response.data.status;
                        pijamas_objeto.offset = response.data.offset;
                        pijamas_objeto.importados = response.data.importados;
                        pijamas_objeto.pendientes = response.data.pendientes;
                        pijamas_objeto.total = response.data.total;

                        // Establece el mensaje correspondiente según el estado
                        let mensaje = "";
                        switch (response.data.status) {
                            case 'capturando_ids':
                                mensaje = "Capturando IDs de todos los productos...";
                                break;
                            case 'importando':
                                mensaje = pijamas_objeto.offset > pijamas_objeto.total ?
                                    "Importando el primer producto..." :
                                    `Se han importado ${pijamas_objeto.importados} productos con éxito de un total de ${pijamas_objeto.total} productos.`;
                                break;
                            case 'finalizado':
                                mensaje = `Se importaron con éxito ${pijamas_objeto.total} productos.`;
                                break;
                        }
                        pijamas_objeto.resultDiv.html(`<p class="woo-ml-success">${mensaje}</p>`);

                        if (pijamas_objeto.importados < pijamas_objeto.total) {
                            import_pijamas(); // Continua importando
                        } else {
                            finalizarProceso(); // Finaliza el proceso cuando todos los productos han sido importados
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Estado del error:", status);
                        console.error("Código de error HTTP:", xhr.status);
                        console.error("Mensaje de error:", error);
                        manejarError(error); // Maneja el error de la solicitud
                    },
                });
            }

            function manejarError(mensaje) {
                if (pijamas_objeto.reintentos < pijamas_objeto.maxReintentos) {
                    pijamas_objeto.reintentos++;
                    let delay = pijamas_objeto.baseDelay * Math.pow(2, pijamas_objeto.reintentos) + Math.random() * 1000;
                    pijamas_objeto.resultDiv.html(`<p class="woo-ml-error">Error en la sincronización: ${mensaje}. Reintentando en ${(delay / 1000).toFixed(2)} segundos (${pijamas_objeto.reintentos}/${pijamas_objeto.maxReintentos})...</p>`);
                    setTimeout(import_pijamas, delay); // Reintenta la importación con retraso
                } else {
                    pijamas_objeto.resultDiv.html('<p class="woo-ml-error">Error en la sincronización tras varios intentos. Por favor, intenta nuevamente más tarde.</p>');
                    finalizarProceso(); // Finaliza el proceso después de los intentos fallidos
                }
            }

            function finalizarProceso() {
                pijamas_objeto.status = "capturando_ids";
                pijamas_objeto.offset = 0;
                pijamas_objeto.importados = 0;
                pijamas_objeto.pendientes = [];
                pijamas_objeto.total = 0;
                pijamas_objeto.reintentos = 0;
            }

            import_pijamas(); // Inicia el proceso de importación al hacer clic
        });
    });
</script>

<script>
    jQuery(document).ready(function($) {
        const retornar_sku_objeto = {
            importados: 0,
            offset: 0,
            total: 0,
            resultDiv: $('#rescatar-sku-result'),
            sinsku: [],
        };

        $('#rescatar-sku').on('click', function() {
            function retornar_sku() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'return_sku',
                        importados: retornar_sku_objeto.importados,
                        offset: retornar_sku_objeto.offset,
                        sinsku: JSON.stringify(retornar_sku_objeto.sinsku),
                    },
                    success: function(response) {
                        retornar_sku_objeto.importados = response.data.importados;
                        retornar_sku_objeto.offset = response.data.offset;
                        retornar_sku_objeto.total = response.data.total;
                        retornar_sku_objeto.sinsku = response.data.sinsku;
                        if (response.data.status == 'importando') {
                            retornar_sku();
                        }
                    },
                    error: function() {
                        retornar_sku_objeto.resultDiv.html(
                            '<p class="woo-ml-error">Error crítico. Por favor, intente nuevamente.</p>'
                        );
                        finalizarProceso();
                    },
                    complete: function() {
                        retornar_sku_objeto.resultDiv.html(
                            '<p class="woo-ml-success">Se han verificado ' + retornar_sku_objeto.importados + ' productos con éxito de un total de ' + retornar_sku_objeto.total + ' productos.</p>'
                        );
                        finalizarProceso();
                    }
                });
            }

            function finalizarProceso() {
                retornar_sku_objeto.importados = 0;
                retornar_sku_objeto.offset = 0;
                retornar_sku_objeto.total = 0;
            }

            retornar_sku(); // Inicia el proceso al hacer click
        });
    });
</script>





<script>
    jQuery(document).ready(function($) {
        const sincronizar_objeto = {
            button: null,
            resultDiv: $('#importacion-categoria-result'),
            importados: 0,
            total: 0,
            pendientes: [],
            maxReintentos: 5,
            reintentos: 0,
            baseDelay: 1000 // Tiempo base en ms para el backoff exponencial
        };

        $('#sync-wc').on('click', function() {
            sincronizar_objeto.button = $(this);
            sincronizar_objeto.button.prop('disabled', true);
            sincronizar_objeto.button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="currentColor"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Sincronizando...');
            sincronizar_objeto.resultDiv.text('Sincronización en progreso...');
            sincronizar_objeto.reintentos = 0;
            sync_all_products();
        });

        function sync_all_products() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'sync_all_products',
                    importados: sincronizar_objeto.importados,
                    pendientes: JSON.stringify(sincronizar_objeto.pendientes),
                },
                success: function(response) {
                    sincronizar_objeto.importados = response.data.importados;
                    sincronizar_objeto.total = response.data.total;
                    sincronizar_objeto.pendientes = response.data.pendientes;
                    sincronizar_objeto.resultDiv.html('<p class="woo-ml-success">Se han importado ' + sincronizar_objeto.importados + ' productos con éxito de un total de ' + sincronizar_objeto.total + ' productos.</p>');
                    sincronizar_objeto.reintentos = 0; // Resetear reintentos tras un éxito

                    if (parseInt(sincronizar_objeto.importados) !== parseInt(sincronizar_objeto.total)) {
                        sync_all_products();
                    } else {
                        finalizar_sincronizacion();
                    }
                },
                error: function(xhr, status, error) {
                    console.log("Estado del error:", status);
                    console.log("Código de error HTTP:", xhr.status);
                    console.log("Mensaje de error:", error);

                    if (sincronizar_objeto.reintentos < sincronizar_objeto.maxReintentos) {
                        sincronizar_objeto.reintentos++;
                        let delay = sincronizar_objeto.baseDelay * Math.pow(2, sincronizar_objeto.reintentos) + Math.random() * 1000; // Agregamos jitter
                        sincronizar_objeto.resultDiv.html('<p class="woo-ml-error">Error en la sincronización. Reintentando en ' + (delay / 1000).toFixed(2) + ' segundos (' + sincronizar_objeto.reintentos + '/' + sincronizar_objeto.maxReintentos + ')...</p>');
                        setTimeout(sync_all_products, delay);
                    } else {
                        sincronizar_objeto.resultDiv.html('<p class="woo-ml-error">Error en la sincronización tras varios intentos. Por favor, intenta nuevamente más tarde.</p>');
                        finalizar_sincronizacion();
                    }
                }
            });
        }

        function finalizar_sincronizacion() {
            sincronizar_objeto.button.prop('disabled', false);
            sincronizar_objeto.button.html('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" class="woo-ml-icon"><path fill="none" d="M0 0h24v24H0z"/><path d="M5.463 4.433A9.961 9.961 0 0 1 12 2c5.523 0 10 4.477 10 10 0 2.136-.67 4.116-1.81 5.74L17 12h3A8 8 0 0 0 6.46 6.228l-.997-1.795zm13.074 15.134A9.961 9.961 0 0 1 12 22C6.477 22 2 17.523 2 12c0-2.136.67-4.116 1.81-5.74L7 12H4a8 8 0 0 0 13.54 5.772l.997 1.795z" fill="currentColor"/></svg> Sincronizar Todos los Productos');
        }
    });
</script>

<!-- Verificar SKU -->
<script>
    jQuery(document).ready(function($) {
        $('#verificar_sku').on('click', function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'verificar_sku',
                },
                success: function(response) {
                    $('#sku_result').html('<p class="woo-ml-success">Se ha generado un informe en el Log con el SKU de los productos en WooCommerce.</p>');
                },
                error: function() {
                    $('#sku_result').html('<p class="woo-ml-error">Error crítico. Por favor, intente nuevamente.</p>');
                },
            });
        });
    });
</script>

<!-- Asignar sku -->
<script>
    jQuery(document).ready(function($) {
        const asignar_sku_objeto = {
            status: 'recopilando',
            exitosos: 0,
            fallidos: [],
            total: 0,
            pendientes: [],
        };

        $('#asignar_sku').on('click', function() {
            finalizarProceso();

            function asignar_sku() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'asignar_sku',
                        status: asignar_sku_objeto.status,
                        exitosos: asignar_sku_objeto.exitosos,
                        fallidos: JSON.stringify(asignar_sku_objeto.fallidos),
                        total: asignar_sku_objeto.total,
                        pendientes: JSON.stringify(asignar_sku_objeto.pendientes),
                    },
                    success: function(response) {
                        asignar_sku_objeto.status = response.data.status;
                        asignar_sku_objeto.exitosos = response.data.exitosos;
                        asignar_sku_objeto.fallidos = response.data.fallidos;
                        asignar_sku_objeto.total = response.data.total;
                        asignar_sku_objeto.pendientes = response.data.pendientes;
                        let procesados = 0;
                        procesados = asignar_sku_objeto.exitosos + asignar_sku_objeto.fallidos.length;

                        let mensaje = "";
                        if (response.data.status === 'recopilando') {
                            $('#sku_result').html('<p class="woo-ml-success">Recopilando IDs de los productos en WooCommerce...</p>');
                            asignar_sku();
                        } else if (response.data.status === 'procesando') {
                            $('#sku_result').html('<p class="woo-ml-success">Se han procesado ' + procesados + ' productos de ' + asignar_sku_objeto.total + ' productos.</p>');
                            asignar_sku();
                        } else if (response.data.status === 'finalizado') {
                            if (response.data.fallidos.length > 0) {
                                mensaje = "Productos fallidos: " + response.data.fallidos.join(', ');
                            }
                            $('#sku_result').html('<p class="woo-ml-success">Se han actualizado los SKU de ' + response.data.exitosos + ' productos de un total de ' + response.data.total + ' productos. ' + mensaje + '</p>');
                            finalizarProceso();
                        }
                    },
                    error: function() {
                        $('#sku_result').html('<p class="woo-ml-error">Error en la comunicación con el servidor.</p>');
                        finalizarProceso();
                    },
                });
            }

            function finalizarProceso() {
                asignar_sku_objeto.status = "recopilando";
                asignar_sku_objeto.exitosos = 0;
                asignar_sku_objeto.fallidos = [];
                asignar_sku_objeto.total = 0;
                asignar_sku_objeto.pendientes = [];
            }

            asignar_sku(); // Inicia el proceso al hacer click
        });
    });
</script>

<!-- Verificar Seller Custom Field -->
<script>
    jQuery(document).ready(function($) {
        const verificar_seller_custom_field_objeto = {
            status: 'recopilando',
            offset: 0,
            importados: 0,
            total: 0,
            sin_sku: [],
            pendientes: [],
            datos: [],
        };

        $('#verificar_seller_custom_field').on('click', function() {
            finalizarProceso();

            function verificar_seller_custom_field() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'verificar_seller_custom_field',
                        status: verificar_seller_custom_field_objeto.status,
                        offset: verificar_seller_custom_field_objeto.offset,
                        importados: verificar_seller_custom_field_objeto.importados,
                        total: verificar_seller_custom_field_objeto.total,
                        sin_sku: JSON.stringify(verificar_seller_custom_field_objeto.sin_sku),
                        pendientes: JSON.stringify(verificar_seller_custom_field_objeto.pendientes),
                        datos: JSON.stringify(verificar_seller_custom_field_objeto.datos),
                    },
                    success: function(response) {
                        verificar_seller_custom_field_objeto.status = response.data.status;
                        verificar_seller_custom_field_objeto.offset = response.data.offset;
                        verificar_seller_custom_field_objeto.importados = response.data.importados;
                        verificar_seller_custom_field_objeto.total = response.data.total;
                        verificar_seller_custom_field_objeto.sin_sku = response.data.sin_sku;
                        verificar_seller_custom_field_objeto.pendientes = response.data.pendientes;
                        verificar_seller_custom_field_objeto.datos = response.data.datos;

                        if (response.data.status == 'recopilando') {
                            $('#seller_custom_field_result').html('<p class="woo-ml-success">Recopilando IDs de los productos a verificar...</p>');
                            verificar_seller_custom_field();
                        }
                        if (response.data.status == 'importando') {
                            $('#seller_custom_field_result').html('<p class="woo-ml-success">Se han procesado ' + verificar_seller_custom_field_objeto.importados + ' productos de ' + verificar_seller_custom_field_objeto.total + ' productos.</p>');
                            verificar_seller_custom_field();
                        }
                        if (response.data.status == 'finalizado') {
                            $('#seller_custom_field_result').html('<p class="woo-ml-success">Se ha generado un informe en el Log con los SELLER CUSTOM FIELD de los productos de Mercadolibre</p>');
                            finalizarProceso();
                        }
                    },
                    error: function() {
                        respaldo_objeto.resultDiv.html(
                            '<p class="woo-ml-error">Error crítico. Por favor, intente nuevamente.</p>'
                        );
                        finalizarProceso();
                    },
                });
            }

            function finalizarProceso() {
                verificar_seller_custom_field_objeto.status = "recopilando";
                verificar_seller_custom_field_objeto.offset = 0;
                verificar_seller_custom_field_objeto.importados = 0;
                verificar_seller_custom_field_objeto.total = 0;
                verificar_seller_custom_field_objeto.sin_sku = [];
                verificar_seller_custom_field_objeto.pendientes = [];
                verificar_seller_custom_field_objeto.datos = [];
            }

            verificar_seller_custom_field(); // Inicia el proceso al hacer click
        });
    });
</script>

<!-- Testing -->
<script> 
    jQuery(document).ready(function ($) {
        $('#testing').on('click', function () {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'testing',
                },
                success: function (response) {
                    console.log("Respuesta cruda:", response); // 👈 esto es clave
                
                    try {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        console.log("Datos parseados:", data);
                        $('#testing_result').html('<pre class="woo-ml-success">' + JSON.stringify(data, null, 2) + '</pre>');
                    } catch (e) {
                        console.error("Error al parsear JSON:", e);
                        $('#testing_result').html('<p class="woo-ml-error">❌ Error al parsear la respuesta: ' + e.message + '</p>');
                    }
                },
                error: function () {
                    $('#testing_result').html('<p class="woo-ml-error"> Error en la funcion, no se porque, pero hay un error </p>');
                },
            });
        });
    });
</script>

<!-- Importarproductosarchivos -->
<script> 
    jQuery(document).ready(function ($) {
        $('#importararchivo').on('click', function () {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'importararchivo',
                },
                success: function (response) {
                    console.log("Respuesta cruda:", response); // 👈 esto es clave
                
                    try {
                        let data = typeof response === 'string' ? JSON.parse(response) : response;
                        console.log("Datos parseados:", data);
                        $('#importarar_result').html('<pre class="woo-ml-success">' + JSON.stringify(data, null, 2) + '</pre>');
                    } catch (e) {
                        console.error("Error al parsear JSON:", e);
                        $('#importarar_result').html('<p class="woo-ml-error">❌ Error al parsear la respuesta: ' + e.message + '</p>');
                    }
                },
                error: function () {
                    $('#importarar_result').html('<p class="woo-ml-error"> Error en la funcion, no se porque, pero hay un error </p>');
                },
            });
        });
    });
</script>


<script>
document.getElementById("woo_ml_importar_categoria").addEventListener("click", function () {
    const categoriaId = document.getElementById("woo_ml_categoria_importar").value.trim();

    if (!categoriaId) {
        alert("❌ Debes ingresar un ID de categoría.");
        return;
    }

    jQuery.post(ajaxurl, {
        action: 'woo_ml_importar_categoria_dinamica',
        categoria_id: categoriaId
    }, function(response) {
        if (response.success) {
            console.log("✅ Resultados:", response.data.resultados);
            // Aquí puedes mostrarlos en pantalla si deseas
        } else {
            alert("⚠️ Error: " + response.data.mensaje);
        }
    });
});

</script>


// <script>
//     document.getElementById('visualizar-variaciones-btn').addEventListener('click', function() {
//     var itemId = document.getElementById('ml-item-id-input').value.trim();
//     var resultado = document.getElementById('resultado-variaciones');
//     resultado.innerHTML = "Cargando...";
//     if (!itemId) {
//         resultado.innerHTML = "Debes ingresar el ID de la publicación de MercadoLibre";
//         return;
//     }
//     var formData = new FormData();
//     formData.append('action', 'skyblue_get_ml_product_variations');
//     formData.append('ml_item_id', itemId);

//     fetch(ajaxurl, {
//         method: 'POST',
//         body: formData
//     })
//     .then(resp => resp.json())
//     .then(data => {
//         if (data.success) {
//             var html = `<h4>${data.data.product.title} (ID: ${data.data.product.ml_item_id})</h4>`;
//             html += "<table border='1'><tr><th>ID Variación</th><th>SKU</th><th>Precio</th><th>Cantidad</th></tr>";
//             data.data.variations.forEach(v => {
//                 html += `<tr><td>${v.variation_id}</td><td>${v.sku ?? ''}</td><td>${v.price}</td><td>${v.quantity}</td></tr>`;
//             });
//             html += "</table>";
//             resultado.innerHTML = html;
//         } else {
//             resultado.innerHTML = "Error: " + (data.data.error || "Desconocido");
//         }
//     })
//     .catch(e => {
//         resultado.innerHTML = "Error en la petición";
//     });
// });

// </script>

<script>
    document.getElementById('woo_ml_importar_archivo').addEventListener('click', function() {
      const resultadoDiv = document.getElementById('resultado_importacion');
      resultadoDiv.textContent = 'Importando productos...';
    
      fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: new URLSearchParams({ action: 'importar_productos_desde_archivo' })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          resultadoDiv.textContent = data.data.mensaje || 'Importación finalizada correctamente.';
        } else {
          resultadoDiv.textContent = data.data.mensaje || 'Error en la importación.';
        }
      })
      .catch(() => {
        resultadoDiv.textContent = 'Error en la petición AJAX.';
      });
    });
</script>

<script>
jQuery(document).ready(function($) {
    const btn = $('#importar_lote_btn');
    const progressDiv = $('#importar_progreso');
    const resultDiv = $('#importar_resultado');

    btn.on('click', function() {
        btn.prop('disabled', true).html('Procesando lote...');
        progressDiv.html('<span style="color: blue;">⏳ Iniciando comunicación con el servidor...</span>');
        resultDiv.html('');

        procesarLote();
    });

    function procesarLote() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'importararchivo',
                _wpnonce: '<?php echo wp_create_nonce("importar_archivo_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    progressDiv.html('<span style="color: green;">' + response.data.mensaje + '</span>');

                    let resultHTML = '';
                    if (response.data.exitosos && response.data.exitosos.length > 0) {
                        resultHTML += '<div><strong>✅ Exitosos:</strong> ' + response.data.exitosos.join(', ') + '</div>';
                    }
                    if (response.data.fallidos && Object.keys(response.data.fallidos).length > 0) {
                        resultHTML += '<div style="margin-top:10px;"><strong>❌ Fallidos:</strong><ul>';
                        for (const [id, razon] of Object.entries(response.data.fallidos)) {
                            resultHTML += `<li>${id}: ${razon}</li>`;
                        }
                        resultHTML += '</ul></div>';
                    }
                    resultDiv.html(resultHTML || 'No hubo resultados en este lote.');

                    if (response.data.completado) {
                        btn.prop('disabled', true).text('¡Proceso Finalizado!');
                        progressDiv.append('<br><strong style="color:green;">¡Todos los productos han sido procesados!</strong>');
                    } else {
                        btn.prop('disabled', false).text('Procesar Siguiente Lote (' + response.data.restantes + ' restantes)');
                    }
                } else {
                    progressDiv.html('<strong style="color:red;">Error:</strong> ' + response.data.mensaje);
                    btn.prop('disabled', false).text('Reintentar Lote');
                }
            },
            error: function() {
                progressDiv.html('<strong style="color:red;">Error de comunicación. Revisa la consola del navegador.</strong>');
                btn.prop('disabled', false).text('Reintentar Lote');
            }
        });
    }
});
</script>

<script>
    jQuery(document).ready(function($) {
        // Manejador del botón de importación
        $('#import-single-product-btn').on('click', function() {
            const button = $(this);
            const logDiv = $('#single-import-log');
            const productId = $('#ml-product-id-input').val().trim();
    
            // Validación simple en el frontend
            if (productId === '') {
                logDiv.html('<span style="color: red;">❌ Por favor, ingresa un ID de publicación de Mercado Libre.</span>');
                return;
            }
    
            // Deshabilitar botón y mostrar estado de carga
            button.prop('disabled', true).html('Importando...');
            logDiv.html('<span style="color: blue;">⏳ Procesando ID: ' + productId + '...</span>');
    
            // Petición AJAX al backend
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'importar_sku_directo',
                    nonce: '<?php echo wp_create_nonce("importar_producto_individual_nonce"); ?>',
                    id_ml: productId
                },
                success: function(response) {
                    if (response.success) {
                        logDiv.html('<span style="color: green;">' + response.data.mensaje + '</span>');
                    } else {
                        logDiv.html('<span style="color: red;">' + response.data.mensaje + '</span>');
                    }
                },
                error: function() {
                    logDiv.html('<span style="color: red;">❌ Error de comunicación con el servidor. Revisa la consola del navegador para más detalles.</span>');
                },
                complete: function() {
                    // Rehabilitar el botón al finalizar
                    button.prop('disabled', false).html('Importar Producto');
                }
            });
        });
    
        // Manejador del botón para limpiar el log
        $('#clear-log-btn').on('click', function() {
            $('#single-import-log').html('Esperando una acción...');
        });
    });
</script>


<!-- NUEVA HERRAMIENTA DE BÚSQUEDA -->
<script>
jQuery(document).ready(function($) {
    const logDiv = $('#search-log');

    // --- Búsqueda por ID de Producto ---
    $('#search-by-id-btn').on('click', function() {
        const button = $(this);
        const itemId = $('#search-item-id-input').val().trim();

        if (!itemId) {
            logDiv.html('<span style="color: red;">❌ Por favor, ingresa un ID de producto.</span>');
            return;
        }

        button.prop('disabled', true).text('Buscando...');
        logDiv.html('<span style="color: blue;">⏳ Buscando producto ' + itemId + '...</span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'woo_ml_buscar_item_por_id', // <-- NOMBRE DE ACCIÓN CORREGIDO
                item_id: itemId,
                _wpnonce: '<?php echo wp_create_nonce("woo_ml_search_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    let content = '<strong>Resultado para ' + itemId + ':</strong>\n\n';
                    for (const [key, value] of Object.entries(response.data.producto)) {
                        if (key === 'Enlace') {
                            content += `<strong>${key}:</strong> <a href="${value}" target="_blank">Ver en ML</a>\n`;
                        } else {
                            content += `<strong>${key}:</strong> ${value}\n`;
                        }
                    }
                    logDiv.html('<pre style="white-space: pre-wrap; color: green;">' + content + '</pre>');
                } else {
                    logDiv.html('<span style="color: red;">' + response.data.mensaje + '</span>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = '<strong>❌ Error de comunicación con el servidor.</strong><br>';
                errorMsg += '<strong>Estado:</strong> ' + textStatus + '<br>';
                errorMsg += '<strong>Error:</strong> ' + errorThrown + '<br>';
                if (jqXHR.responseText) {
                    errorMsg += '<strong>Respuesta del servidor:</strong><div style="background-color: #ffecec; border: 1px solid #f5c6cb; padding: 10px; margin-top: 5px; border-radius: 4px; font-family: monospace; white-space: pre-wrap;">' + jqXHR.responseText.substring(0, 1000) + '</div>';
                }
                logDiv.html('<div style="color: red;">' + errorMsg + '</div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Buscar Producto');
            }
        });
    });

    // --- Búsqueda por ID de Categoría ---
    $('#search-by-category-btn').on('click', function() {
        const button = $(this);
        const categoryId = $('#search-category-id-input').val().trim();

        if (!categoryId) {
            logDiv.html('<span style="color: red;">❌ Por favor, ingresa un ID de categoría.</span>');
            return;
        }

        button.prop('disabled', true).text('Buscando...');
        logDiv.html('<span style="color: blue;">⏳ Buscando productos en la categoría ' + categoryId + '...</span>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'tomaridcategorias_ajax', // <-- NOMBRE DE ACCIÓN CORREGIDO
                categoria_objetivo: categoryId,
                _wpnonce: '<?php echo wp_create_nonce("woo_ml_search_nonce"); ?>'
            },
            success: function(response) {
                if (response.success) {
                    let content = '<strong>' + response.data.mensaje + '</strong>\n\n';
                    if (response.data.productos.length > 0) {
                        response.data.productos.forEach(prod => {
                            content += `<strong>- Título:</strong> ${prod['Título']}\n`;
                            content += `  <strong>ID:</strong> ${prod['ID de Publicación']}\n`;
                            content += `  <strong>Enlace:</strong> <a href="${prod['Enlace']}" target="_blank">Ver en ML</a>\n\n`;
                        });
                    } else {
                        content += "No se encontraron productos activos en esta categoría.";
                    }
                    logDiv.html('<pre style="white-space: pre-wrap; color: green;">' + content + '</pre>');
                } else {
                    logDiv.html('<span style="color: red;">' + response.data.mensaje + '</span>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                let errorMsg = '<strong>❌ Error de comunicación con el servidor.</strong><br>';
                errorMsg += '<strong>Estado:</strong> ' + textStatus + '<br>';
                errorMsg += '<strong>Error:</strong> ' + errorThrown + '<br>';
                if (jqXHR.responseText) {
                    errorMsg += '<strong>Respuesta del servidor:</strong><div style="background-color: #ffecec; border: 1px solid #f5c6cb; padding: 10px; margin-top: 5px; border-radius: 4px; font-family: monospace; white-space: pre-wrap;">' + jqXHR.responseText.substring(0, 1000) + '</div>';
                }
                logDiv.html('<div style="color: red;">' + errorMsg + '</div>');
            },
            complete: function() {
                button.prop('disabled', false).text('Buscar en Categoría');
            }
        });
    });

    // --- Limpiar el Log ---
    $('#clear-search-log-btn').on('click', function() {
        logDiv.html('Esperando una búsqueda...');
    });
});
</script>



