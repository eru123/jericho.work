import { createRouter, createWebHistory } from 'vue-router'

const routes = [
    {
        path: '/',
        redirect: () => {
            // add logic for auth check here
            return {
                path: '/overview',
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
                path: '/overview',
                name: 'Overview',
                component: () => import('@/views/Overview.vue')
            }
        ]
    }

]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
