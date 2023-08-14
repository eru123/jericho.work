import { createRouter, createWebHistory } from 'vue-router'
import Landing from '@/views/Landing.vue'

import MDLayout from '@/layouts/PublicMarkdown.vue'
import MDPrivacyPolicy from '@/md/privacy-policy.md'
import MDTermsAndConditions from '@/md/terms-and-conditions.md'
import Verify from '@/views/Verify.vue'

const routes = [
    {
        path: '/',
        name: 'Landing',
        component: Landing
    },
    {
        path: '/',
        name: 'Markdown',
        component: MDLayout,
        children: [
            {
                path: 'privacy-policy',
                name: 'PrivacyPolicy',
                component: MDPrivacyPolicy
            },
            {
                path: 'terms-and-conditions',
                name: 'TermsAndConditions',
                component: MDTermsAndConditions
            }
        ]
    },
    {
        path: '/verify/:token',
        name: 'Verify',
        component: Verify
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
})

export const paths = routes.reduce((all, cur) => cur?.children ? all.concat(cur.children.map(c => c.path.charAt(0) === '/' ? c.path : cur.path + c.path)) : all.concat(cur.path.charAt(0) === '/' ? cur.path : `/${cur.path}`), []).map(p => p.toLowerCase());
export default router
