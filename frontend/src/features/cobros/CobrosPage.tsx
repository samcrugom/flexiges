import { useState } from 'react'
import { useCobros, useCobrar } from '@/hooks/queries'
import { PageHeader, Card, DataTable, EstadoBadge, Btn, Field, inputCls, Pagination, StatCard } from '@/components/shared'
import { fmtEur, fmtDate } from '@/utils'
import { X, Euro } from 'lucide-react'
import type { Cobro } from '@/types'

export default function CobrosPage() {
  const [estado, setEstado] = useState('pendiente')
  const [page, setPage] = useState(1)
  const [modal, setModal] = useState<Cobro|null>(null)
  const [importe, setImporte] = useState('')

  const { data, isLoading } = useCobros({ estado: estado||undefined, page, per_page:30 })
  const cobrarMut = useCobrar()

  const pendientes = data?.data?.filter((c:Cobro)=>c.estado==='pendiente'||c.estado==='parcial') ?? []
  const totalPend  = pendientes.reduce((s:number,c:Cobro)=>s+Number(c.importe_total)-Number(c.importe_cobrado),0)
  const vencidos   = pendientes.filter((c:Cobro)=>new Date(c.fecha_vencimiento)<new Date())

  const openCobrar = (c:Cobro) => {
    setModal(c)
    setImporte(String(Math.round((Number(c.importe_total)-Number(c.importe_cobrado))*100)/100))
  }

  const confirmar = () => {
    cobrarMut.mutate({ id: modal!.id, importe: Number(importe) }, { onSuccess: ()=>setModal(null) })
  }

  const cols = [
    { key:'numero',    label:'Recibo',   render:(r:Cobro)=><span className="font-mono text-[12px]">{r.numero}</span> },
    { key:'factura',   label:'Factura',  render:(r:Cobro)=><span className="font-mono text-[12px]">{r.factura?.numero??'—'}</span> },
    { key:'cliente',   label:'Cliente',  render:(r:Cobro)=><span className="font-medium">{r.cliente?.nombre}</span> },
    { key:'importe_total', label:'Total', align:'right' as const, render:(r:Cobro)=><span className="font-semibold">{fmtEur(r.importe_total)}</span> },
    { key:'importe_cobrado', label:'Cobrado', align:'right' as const, render:(r:Cobro)=>fmtEur(r.importe_cobrado) },
    { key:'pend', label:'Pendiente', align:'right' as const, render:(r:Cobro)=>{
      const p=Number(r.importe_total)-Number(r.importe_cobrado)
      return <span className={`font-semibold ${p>0?'text-red-600':'text-[#2D6A4F]'}`}>{fmtEur(p)}</span>
    }},
    { key:'fecha_vencimiento', label:'Vencimiento', render:(r:Cobro)=>{
      const venc=new Date(r.fecha_vencimiento)<new Date()&&r.estado!=='cobrado'
      return <span className={venc?'text-red-600 font-semibold':''}>{fmtDate(r.fecha_vencimiento)}{venc?' ⚠':''}</span>
    }},
    { key:'estado',    label:'Estado',   render:(r:Cobro)=><EstadoBadge estado={r.estado}/> },
    { key:'_', label:'', render:(r:Cobro)=>r.estado!=='cobrado'&&r.estado!=='anulado'?(
      <Btn size="sm" onClick={e=>{e.stopPropagation();openCobrar(r)}}>Cobrar</Btn>
    ):null },
  ]

  const ESTADOS = [
    { id:'', label:'Todos' },
    { id:'pendiente', label:'Pendientes' },
    { id:'parcial', label:'Parciales' },
    { id:'cobrado', label:'Cobrados' },
  ]

  return (
    <div>
      <PageHeader title="Cobros" sub="Cartera de cobros y vencimientos"/>

      <div className="grid grid-cols-2 xl:grid-cols-4 gap-3 mb-5">
        <StatCard label="Total pendiente"    value={fmtEur(totalPend)}          color="text-red-600"/>
        <StatCard label="Recibos pendientes" value={pendientes.length}           sub="en cartera"/>
        <StatCard label="Vencidos"           value={vencidos.length}             color={vencidos.length>0?'text-red-600':'text-[#1A1916]'} sub="sin cobrar"/>
        <StatCard label="Cobrados este mes"  value={data?.data?.filter((c:Cobro)=>c.estado==='cobrado').length??0}/>
      </div>

      {/* Filtro estado */}
      <div className="flex gap-1.5 mb-4">
        {ESTADOS.map(e=>(
          <button key={e.id} onClick={()=>{setEstado(e.id);setPage(1)}}
            className={`px-3 py-1.5 rounded-full text-[12px] font-medium border transition-colors ${estado===e.id?'bg-[#2D6A4F] text-white border-[#2D6A4F]':'bg-white text-[#6B6860] border-[#E5E3DC] hover:bg-[#F0EFE9]'}`}>
            {e.label}
          </button>
        ))}
      </div>

      <Card noPad>
        <DataTable cols={cols} rows={data?.data??[]} loading={isLoading} keyFn={r=>r.id}/>
      </Card>
      <Pagination page={page} lastPage={data?.last_page??1} onChange={setPage}/>

      {/* Modal cobrar */}
      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-sm">
            <div className="flex items-center justify-between px-5 py-4 border-b border-[#E5E3DC]">
              <h3 className="text-[16px] font-semibold">Registrar cobro</h3>
              <button onClick={()=>setModal(null)} className="p-1.5 rounded hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-5">
              <div className="mb-4 p-3 bg-[#F7F6F3] rounded-[8px]">
                <div className="text-[12px] text-[#6B6860]">Factura</div>
                <div className="font-semibold">{modal.factura?.numero}</div>
                <div className="text-[13px] mt-1">Pendiente: <strong className="text-red-600">{fmtEur(Number(modal.importe_total)-Number(modal.importe_cobrado))}</strong></div>
              </div>
              <Field label="Importe cobrado (€)">
                <input type="number" className={`${inputCls} text-[20px] font-semibold h-12`} step="0.01" min="0.01" value={importe} onChange={e=>setImporte(e.target.value)}/>
              </Field>
              {Number(importe)>0 && Number(importe)<(Number(modal.importe_total)-Number(modal.importe_cobrado)) && (
                <div className="mt-2 p-2.5 bg-amber-50 border border-amber-200 rounded-[7px] text-[12px] text-amber-800">
                  Cobro parcial. Quedará pendiente: {fmtEur((Number(modal.importe_total)-Number(modal.importe_cobrado))-Number(importe))}
                </div>
              )}
            </div>
            <div className="flex gap-2 justify-end px-5 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(null)}>Cancelar</Btn>
              <Btn loading={cobrarMut.isPending} onClick={confirmar} disabled={!importe||Number(importe)<=0}>
                <Euro size={14}/>Confirmar cobro
              </Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
