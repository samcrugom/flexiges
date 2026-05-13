import { createBrowserRouter, Navigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import AppLayout from '@/components/layout/AppLayout'
import LoginPage from '@/features/auth/LoginPage'
import DashboardPage from '@/features/dashboard/DashboardPage'
import ClientesPage from '@/features/clientes/ClientesPage'
import ProveedoresPage from '@/features/proveedores/ProveedoresPage'
import ArticulosPage from '@/features/articulos/ArticulosPage'
import AlmacenPage from '@/features/almacen/AlmacenPage'
import FabricacionPage from '@/features/fabricacion/FabricacionPage'
import VentasPage from '@/features/ventas/VentasPage'
import CobrosPage from '@/features/cobros/CobrosPage'
import AdminPage from '@/features/admin/AdminPage'

function RequireAuth({ children }: { children: React.ReactNode }) {
  const token = useAuthStore(s => s.token)
  if (!token) return <Navigate to="/login" replace />
  return <>{children}</>
}

export const router = createBrowserRouter([
  { path: '/login', element: <LoginPage /> },
  {
    path: '/',
    element: <RequireAuth><AppLayout /></RequireAuth>,
    children: [
      { index: true,            element: <Navigate to="/dashboard" replace /> },
      { path: 'dashboard',      element: <DashboardPage /> },
      { path: 'clientes',       element: <ClientesPage /> },
      { path: 'proveedores',    element: <ProveedoresPage /> },
      { path: 'articulos',      element: <ArticulosPage /> },
      { path: 'almacen',        element: <AlmacenPage /> },
      { path: 'fabricacion',    element: <FabricacionPage /> },
      { path: 'ventas',         element: <VentasPage /> },
      { path: 'cobros',         element: <CobrosPage /> },
      { path: 'admin',          element: <AdminPage /> },
    ],
  },
  { path: '*', element: <Navigate to="/dashboard" replace /> },
])
