import { useDashboard } from '@/hooks/queries'
import { StatCard, Card, EstadoBadge, PageHeader } from '@/components/shared'
import { fmtEur, fmtDate } from '@/utils'
import { AlertTriangle, TrendingUp, Clock } from 'lucide-react'
import { useNavigate } from 'react-router-dom'
import { BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer, CartesianGrid } from 'recharts'

export default function DashboardPage() {
  const { data, isLoading } = useDashboard()
  const navigate = useNavigate()
  const k = data?.kpis

  return (
    <div>
      <PageHeader title="Panel de control" sub={`Resumen del negocio — ${fmtDate(new Date().toISOString())}`} />

      {/* KPIs */}
      <div className="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
        <StatCard label="Cobros pendientes" value={fmtEur(k?.cobros_pendientes ?? 0)} sub={`${k?.num_cobros_pendientes ?? 0} facturas`} color="text-red-600" onClick={()=>navigate('/cobros')}/>
        <StatCard label="Ventas este mes" value={fmtEur(k?.ventas_mes ?? 0)} sub="IVA incluido" color="text-[#2D6A4F]"/>
        <StatCard label="Bajo mínimo" value={k?.articulos_bajo_minimo ?? 0} sub="artículos" color={(k?.articulos_bajo_minimo??0)>0?'text-amber-600':'text-[#1A1916]'} onClick={()=>navigate('/almacen')}/>
        <StatCard label="Órdenes fab. activas" value={k?.ordenes_fab_activas ?? 0} sub="en proceso" onClick={()=>navigate('/fabricacion')}/>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-4">
        {/* Ventas mensuales */}
        <Card>
          <div className="text-[14px] font-semibold mb-4">Ventas últimos 6 meses</div>
          {data?.ventas_mensuales?.length ? (
            <ResponsiveContainer width="100%" height={180}>
              <BarChart data={data.ventas_mensuales} margin={{left:-10}}>
                <CartesianGrid strokeDasharray="3 3" stroke="#E5E3DC"/>
                <XAxis dataKey="mes_label" tick={{fontSize:11,fill:'#6B6860'}}/>
                <YAxis tick={{fontSize:11,fill:'#6B6860'}} tickFormatter={v=>fmtEur(v,0)}/>
                <Tooltip formatter={(v:any)=>fmtEur(v)} labelStyle={{fontSize:12}} contentStyle={{fontSize:12,borderRadius:8,border:'1px solid #E5E3DC'}}/>
                <Bar dataKey="total" fill="#2D6A4F" radius={[4,4,0,0]}/>
              </BarChart>
            </ResponsiveContainer>
          ) : <div className="h-[180px] flex items-center justify-center text-[#6B6860] text-[13px]">Sin datos</div>}
        </Card>

        {/* Top clientes */}
        <Card>
          <div className="text-[14px] font-semibold mb-4">Top clientes del mes</div>
          {(data?.top_clientes ?? []).map((c:any, i:number) => (
            <div key={c.id} className="flex items-center justify-between py-2 border-b border-[#E5E3DC] last:border-0">
              <div className="flex items-center gap-2.5">
                <div className="w-6 h-6 rounded-full bg-[#2D6A4F] text-white text-[11px] font-bold flex items-center justify-center flex-shrink-0">{i+1}</div>
                <div>
                  <div className="text-[13px] font-medium">{c.nombre.split(' ').slice(0,3).join(' ')}</div>
                  <div className="text-[11px] text-[#6B6860]">{c.num_facturas} facturas</div>
                </div>
              </div>
              <div className="text-[13px] font-semibold">{fmtEur(c.total)}</div>
            </div>
          ))}
          {!data?.top_clientes?.length && <div className="py-8 text-center text-[13px] text-[#6B6860]">Sin datos este mes</div>}
        </Card>
      </div>

      <div className="grid grid-cols-1 xl:grid-cols-2 gap-4">
        {/* Bajo mínimo */}
        <Card>
          <div className="flex items-center gap-2 mb-4">
            <AlertTriangle size={15} className="text-amber-500"/>
            <span className="text-[14px] font-semibold">Artículos bajo mínimo</span>
          </div>
          {(data?.bajos_minimo ?? []).length === 0
            ? <p className="text-[13px] text-[#6B6860] py-4 text-center">✓ Todo el stock en niveles correctos</p>
            : (data?.bajos_minimo ?? []).map((a:any) => (
              <div key={a.id} className="flex items-center justify-between py-2 border-b border-[#E5E3DC] last:border-0">
                <div>
                  <div className="text-[13px] font-medium">{a.descripcion}</div>
                  <div className="text-[11px] text-[#6B6860]">Mín: {a.stock_minimo} {a.unidad_medida}</div>
                </div>
                <span className="text-[12px] font-semibold text-red-600">{Number(a.stock_total)<=0?'Sin stock':`${a.stock_total} ${a.unidad_medida}`}</span>
              </div>
            ))
          }
        </Card>

        {/* Cobros próximos */}
        <Card>
          <div className="flex items-center gap-2 mb-4">
            <Clock size={15} className="text-[#2D6A4F]"/>
            <span className="text-[14px] font-semibold">Cobros próximos (7 días)</span>
          </div>
          {(data?.cobros_proximos ?? []).length === 0
            ? <p className="text-[13px] text-[#6B6860] py-4 text-center">Sin cobros próximos</p>
            : (data?.cobros_proximos ?? []).map((c:any) => (
              <div key={c.id} className="flex items-center justify-between py-2 border-b border-[#E5E3DC] last:border-0">
                <div>
                  <div className="text-[13px] font-medium">{c.cliente_nombre?.split(' ').slice(0,3).join(' ')}</div>
                  <div className="text-[11px] text-[#6B6860]">{c.factura_numero}</div>
                </div>
                <div className="text-right">
                  <div className="text-[13px] font-semibold">{fmtEur(c.importe_pendiente)}</div>
                  <div className={`text-[11px] ${c.vencido?'text-red-600 font-medium':'text-[#6B6860]'}`}>{fmtDate(c.fecha_vencimiento)}{c.vencido?' ⚠':''}</div>
                </div>
              </div>
            ))
          }
        </Card>
      </div>
    </div>
  )
}
