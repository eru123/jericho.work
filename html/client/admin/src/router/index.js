import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        redirect: () => {
            // add logic for auth check here
            return {
                path: '/general',
            }
        }
    },
    // Settings
    {
        path: '/',
        name: 'Settings',
        component: () => import('@/layouts/Settings.vue'),
        children: [
            {
                path: '/general',
                name: 'General',
                component: () => import('@/views/General.vue')
            },

            {
                path: '/cdn',
                name: 'CDN',
                component: () => import('@/views/CDN.vue')
            }
        ]
    }

]

const router = createRouter({
    base: '/admin',
    history: createWebHistory('/admin'),
    routes
})

export default router
