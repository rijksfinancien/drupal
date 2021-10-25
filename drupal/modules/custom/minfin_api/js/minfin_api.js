window.onload = function() {

  // Build a system.
  window.ui = SwaggerUIBundle({
    url: drupalSettings.minfin_api.url,
    dom_id: '#swagger-ui',
    //validatorUrl: null,
    presets: [
      SwaggerUIBundle.presets.apis,
      SwaggerUIStandalonePreset.slice(1)
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
    layout: "StandaloneLayout"
  });

};
