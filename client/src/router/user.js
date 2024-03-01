import UserLayout from '@/layouts/UserLayout.vue'

export default [
    {
        path: '/',
        name: 'UserLayout',
        component: UserLayout,
        children: [
            {
                path: 'profile',
                name: 'Profile',
                component: () => import('@/views/Profile.vue')
            },
            // {
            //     path: 'dashboard',
            //     name: 'Dashboard',
            //     component: () => import('@/views/Dashboard.vue')
            // }
        ]
    }
]