export default [
    {
        path: '/',
        name: 'UserLayout',
        component: () => import('@/layouts/UserLayout.vue'),
        children: [
            {
                path: 'profile',
                name: 'Profile',
                component: () => import('@/views/Profile.vue')
            },
            {
                path: 'dashboard',
                name: 'Dashboard',
            }
        ]
    }
]