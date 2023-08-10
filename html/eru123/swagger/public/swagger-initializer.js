function init() {
  window.ui = SwaggerUIBundle({
    url: SWAGGER_URL,
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUIBundle.presets.apis,
    ],
    plugins: [
      SwaggerUIBundle.plugins.DownloadUrl
    ],
  });
}