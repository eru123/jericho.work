import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import './style.css'
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const app = createApp(App)
const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'dark',
    },
})

app.use(router)
app.use(vuetify)
app.mount('#app')
