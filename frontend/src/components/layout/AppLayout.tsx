import { Outlet, NavLink, useNavigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { authApi } from '@/api'
import { useDashboard } from '@/hooks/queries'
import { cn } from '@/utils'
import {
  LayoutDashboard, Users, Package, Warehouse, Factory,
  FileText, Clock, Truck, Settings, LogOut, TrendingUp, ChevronRight
} from 'lucide-react'

const NAV = [
  { to:'/dashboard',   label:'Inicio',        icon:LayoutDashboard, section:null },
  { section:'Comercial' },
  { to:'/clientes',    label:'Clientes',       icon:Users,      badge:'cobros' },
  { to:'/ventas',      label:'Ventas',         icon:FileText },
  { to:'/cobros',      label:'Cobros',         icon:Clock,      badge:'cobros_num' },
  { section:'Catálogo' },
  { to:'/articulos',   label:'Artículos',      icon:Package,    badge:'stock' },
  { to:'/proveedores', label:'Proveedores',    icon:Truck },
  { section:'Operaciones' },
  { to:'/almacen',     label:'Almacén',        icon:Warehouse,  badge:'stock_num' },
  { to:'/fabricacion', label:'Fabricación',    icon:Factory },
  { divider: true },
  { to:'/estadisticas',label:'Estadísticas',   icon:TrendingUp },
  { to:'/admin',       label:'Administración', icon:Settings },
]

export default function AppLayout() {
  const { user, logout } = useAuthStore()
  const navigate = useNavigate()
  const { data: dash } = useDashboard()

  const artBajo = dash?.bajos_minimo?.length ?? 0
  const cobPend = dash?.kpis?.num_cobros_pendientes ?? 0

  const handleLogout = async () => {
    try { await authApi.logout() } catch {}
    logout()
    navigate('/login')
  }

  return (
    <div className="flex h-screen overflow-hidden bg-[#F7F6F3]">
      {/* Sidebar */}
      <aside className="w-[220px] bg-white border-r border-[#E5E3DC] flex flex-col flex-shrink-0 overflow-y-auto">
        {/* Logo */}
        <div className="px-4 pt-5 pb-3">
          <div className="text-[20px] font-semibold tracking-tight text-[#1A1916]">FlexiGES</div>
          <div className="text-[11px] text-[#6B6860] font-normal">Gestión Comercial</div>
        </div>

        {/* Nav */}
        <nav className="flex-1 pb-3">
          {NAV.map((item, i) => {
            if ('section' in item && item.section) {
              return <div key={i} className="px-3 pt-3 pb-1 text-[10px] font-semibold text-[#6B6860] uppercase tracking-widest">{item.section}</div>
            }
            if ('divider' in item) {
              return <div key={i} className="mx-3 my-2 h-px bg-[#E5E3DC]" />
            }
            if (!item.to) return null
            const Icon = (item as any).icon
            const badgeCount = (item as any).badge === 'stock_num' ? artBajo
              : (item as any).badge === 'cobros_num' ? cobPend : 0

            return (
              <NavLink key={item.to} to={item.to!} className={({ isActive }) =>
                cn('flex items-center gap-2.5 px-3 py-2 mx-1.5 rounded-[7px] text-[13px] font-normal transition-all duration-150 border-none',
                  isActive ? 'bg-[#2D6A4F] text-white font-medium' : 'text-[#6B6860] hover:bg-[#F0EFE9] hover:text-[#1A1916]')
              }>
                {({ isActive }) => (
                  <>
                    <Icon size={16} className={cn('flex-shrink-0', isActive ? 'opacity-100' : 'opacity-70')} />
                    <span className="flex-1">{(item as any).label}</span>
                    {badgeCount > 0 && (
                      <span className="min-w-[18px] h-[18px] rounded-full bg-red-500 text-white text-[10px] font-semibold flex items-center justify-center px-1">
                        {badgeCount}
                      </span>
                    )}
                  </>
                )}
              </NavLink>
            )
          })}
        </nav>

        {/* User */}
        <div className="border-t border-[#E5E3DC] p-3">
          <div className="flex items-center gap-2 mb-2">
            <div className="w-8 h-8 rounded-full bg-[#2D6A4F] text-white flex items-center justify-center text-[13px] font-bold flex-shrink-0">
              {user?.name?.[0]?.toUpperCase()}
            </div>
            <div className="flex-1 min-w-0">
              <div className="text-[12px] font-medium text-[#1A1916] truncate">{user?.name}</div>
              <div className="text-[10px] text-[#6B6860] truncate">{user?.roles?.[0]}</div>
            </div>
          </div>
          <button onClick={handleLogout} className="flex items-center gap-2 text-[12px] text-[#6B6860] hover:text-red-600 transition-colors w-full">
            <LogOut size={13} /> Cerrar sesión
          </button>
        </div>
      </aside>

      {/* Main */}
      <main className="flex-1 overflow-hidden flex flex-col">
        <div className="flex-1 overflow-y-auto p-6">
          <Outlet />
        </div>
      </main>
    </div>
  )
}
