import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('../components/AuthView.vue'),
        meta: { guestOnly: true },
    },
    {
        path: '/',
        component: () => import('../layouts/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '', redirect: { name: 'dashboard' } },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('../views/DashboardView.vue'),
            },
            {
                path: 'cotizaciones',
                name: 'cotizaciones',
                component: () => import('../views/cotizaciones/CotizacionesListView.vue'),
            },
            {
                path: 'cotizaciones/:id',
                name: 'cotizaciones.show',
                component: () => import('../views/cotizaciones/CotizacionDetailView.vue'),
                props: true,
            },
            {
                path: 'cotizaciones/:id/editar',
                name: 'cotizaciones.editar',
                component: () => import('../views/cotizaciones/CotizacionFormView.vue'),
                props: true,
            },
            {
                path: 'ventas',
                name: 'ventas',
                component: () => import('../views/ventas/VentasListView.vue'),
            },
            {
                path: 'ventas/:id',
                name: 'ventas.show',
                component: () => import('../views/ventas/VentaDetailView.vue'),
                props: true,
            },
            {
                path: 'clientes',
                name: 'clientes',
                component: () => import('../views/clientes/ClientesListView.vue'),
            },
            {
                path: 'clientes/:id',
                name: 'clientes.show',
                component: () => import('../views/clientes/ClienteDetailView.vue'),
                props: true,
            },
            {
                path: 'productos',
                name: 'productos',
                component: () => import('../views/productos/ProductosListView.vue'),
            },
            {
                path: 'empresa',
                name: 'empresa',
                component: () => import('../views/EmpresaView.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login' };
    }

    if (to.meta.guestOnly && auth.isAuthenticated) {
        return { name: 'dashboard' };
    }

    return true;
});

export default router;
