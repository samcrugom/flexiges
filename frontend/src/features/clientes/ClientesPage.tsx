import { useState } from 'react'
import { useClientes, useCreateCliente, useUpdateCliente, useDeleteCliente } from '@/hooks/queries'
import { useMaestros } from '@/hooks/queries'
import { PageHeader, SearchInput, Card, DataTable, EstadoBadge, Btn, Field, inputCls, selectCls, ConfirmDialog, Pagination } from '@/components/shared'
import { fmtEur } from '@/utils'
import { Plus, Edit2, Trash2, X } from 'lucide-react'
import type { Cliente } from '@/types'

const empty: Partial<Cliente> = { tipo_cliente:'N', tarifa:1, descuento_pct:0, activo:true, credito_maximo:0 }

export default function ClientesPage() {
  const [search, setSearch] = useState('')
  const [page, setPage]     = useState(1)
  const [modal, setModal]   = useState(false)
  const [editing, setEditing] = useState<Cliente|null>(null)
  const [form, setForm]     = useState<Partial<Cliente>>(empty)
  const [delId, setDelId]   = useState<number|null>(null)

  const { data, isLoading } = useClientes({ search, page, per_page:25 })
  const { formasPago, vendedores, zonas, sectores } = useMaestros()
  const createMut  = useCreateCliente()
  const updateMut  = useUpdateCliente(editing?.id ?? 0)
  const deleteMut  = useDeleteCliente()

  const openNew  = () => { setForm(empty); setEditing(null); setModal(true) }
  const openEdit = (c: Cliente) => { setForm({...c}); setEditing(c); setModal(true) }
  const set = (k: string, v: any) => setForm(f => ({...f,[k]:v}))

  const save = () => {
    const mut = editing ? updateMut : createMut
    ;(mut as any).mutate(form, { onSuccess: () => setModal(false) })
  }

  const cols = [
    { key:'codigo', label:'Código', render:(r:Cliente)=><span className="font-mono text-[12px]">{r.codigo}</span> },
    { key:'nombre', label:'Nombre', render:(r:Cliente)=><span className="font-medium">{r.nombre}</span> },
    { key:'nif', label:'NIF', render:(r:Cliente)=><span className="font-mono text-[12px]">{r.nif||'—'}</span> },
    { key:'localidad', label:'Localidad' },
    { key:'tipo_cliente', label:'Tipo', render:(r:Cliente)=><span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border ${r.tipo_cliente==='R'?'bg-orange-100 text-orange-800 border-orange-300':'bg-blue-100 text-blue-800 border-blue-300'}`}>{r.tipo_cliente==='R'?'Rec. Equiv.':'Normal'}</span> },
    { key:'forma_pago', label:'F. Pago', render:(r:Cliente)=>r.forma_pago?.nombre||'—' },
    { key:'saldo_pendiente', label:'Saldo', align:'right' as const, render:(r:Cliente)=><span className={`font-medium ${Number(r.saldo_pendiente)>0?'text-red-600':'text-[#2D6A4F]'}`}>{fmtEur(r.saldo_pendiente)}</span> },
    { key:'_', label:'', render:(r:Cliente)=>(
      <div className="flex gap-1">
        <button onClick={e=>{e.stopPropagation();openEdit(r)}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860] transition-colors"><Edit2 size={13}/></button>
        <button onClick={e=>{e.stopPropagation();setDelId(r.id)}} className="p-1.5 rounded-[6px] border border-[#E5E3DC] bg-white hover:bg-[#F0EFE9] text-[#6B6860] transition-colors"><Trash2 size={13}/></button>
      </div>
    )},
  ]

  return (
    <div>
      <PageHeader title="Clientes" sub={`${data?.total??0} clientes en cartera`} actions={
        <div className="flex gap-2">
          <SearchInput value={search} onChange={v=>{setSearch(v);setPage(1)}} placeholder="Buscar por nombre, código, NIF..."/>
          <Btn onClick={openNew}><Plus size={14}/>Nuevo cliente</Btn>
        </div>
      }/>

      <Card noPad>
        <DataTable cols={cols} rows={data?.data??[]} loading={isLoading} keyFn={r=>r.id}/>
      </Card>
      <Pagination page={page} lastPage={data?.last_page??1} onChange={setPage}/>

      {/* Modal form */}
      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&setModal(false)}>
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="flex items-center justify-between px-6 py-4 border-b border-[#E5E3DC] sticky top-0 bg-white z-10">
              <h2 className="text-[16px] font-semibold">{editing?'Editar cliente':'Nuevo cliente'}</h2>
              <button onClick={()=>setModal(false)} className="p-1.5 rounded-[6px] hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-6">
              <div className="grid grid-cols-3 gap-3">
                <Field label="Código"><input className={inputCls} value={form.codigo||''} onChange={e=>set('codigo',e.target.value)} placeholder="0001"/></Field>
                <div className="col-span-2"><Field label="Nombre / Razón social *"><input className={inputCls} value={form.nombre||''} onChange={e=>set('nombre',e.target.value)}/></Field></div>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="NIF / CIF"><input className={inputCls} value={form.nif||''} onChange={e=>set('nif',e.target.value)}/></Field>
                <Field label="Email"><input type="email" className={inputCls} value={form.email||''} onChange={e=>set('email',e.target.value)}/></Field>
              </div>
              <Field label="Dirección"><input className={inputCls} value={form.direccion||''} onChange={e=>set('direccion',e.target.value)}/></Field>
              <div className="grid grid-cols-3 gap-3">
                <Field label="Cód. Postal"><input className={inputCls} value={form.codigo_postal||''} onChange={e=>set('codigo_postal',e.target.value)}/></Field>
                <Field label="Localidad"><input className={inputCls} value={form.localidad||''} onChange={e=>set('localidad',e.target.value)}/></Field>
                <Field label="Provincia"><input className={inputCls} value={form.provincia||''} onChange={e=>set('provincia',e.target.value)}/></Field>
              </div>
              <div className="grid grid-cols-2 gap-3">
                <Field label="Teléfono"><input className={inputCls} value={form.telefono||''} onChange={e=>set('telefono',e.target.value)}/></Field>
                <Field label="Persona contacto"><input className={inputCls} value={(form as any).persona_contacto||''} onChange={e=>set('persona_contacto',e.target.value)}/></Field>
              </div>
              <div className="bg-[#F7F6F3] rounded-[8px] p-4 mb-3">
                <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-wider mb-3">Condiciones comerciales</div>
                <div className="grid grid-cols-3 gap-3">
                  <Field label="Tipo cliente">
                    <select className={selectCls} value={form.tipo_cliente||'N'} onChange={e=>set('tipo_cliente',e.target.value)}>
                      <option value="N">Normal</option>
                      <option value="R">Recargo de Equivalencia</option>
                      <option value="E">Exento IVA</option>
                    </select>
                  </Field>
                  <Field label="Forma de pago">
                    <select className={selectCls} value={form.forma_pago_id||''} onChange={e=>set('forma_pago_id',Number(e.target.value))}>
                      <option value="">Sin asignar</option>
                      {formasPago.data?.map((fp:any)=><option key={fp.id} value={fp.id}>{fp.nombre}</option>)}
                    </select>
                  </Field>
                  <Field label="Tarifa precios">
                    <select className={selectCls} value={form.tarifa||1} onChange={e=>set('tarifa',Number(e.target.value))}>
                      {[1,2,3,4].map(t=><option key={t} value={t}>Tarifa {t}</option>)}
                    </select>
                  </Field>
                </div>
                <div className="grid grid-cols-3 gap-3">
                  <Field label="Dto. habitual (%)"><input type="number" className={inputCls} min="0" max="100" step="0.5" value={form.descuento_pct||0} onChange={e=>set('descuento_pct',Number(e.target.value))}/></Field>
                  <Field label="Vendedor">
                    <select className={selectCls} value={form.vendedor_id||''} onChange={e=>set('vendedor_id',Number(e.target.value))}>
                      <option value="">Sin asignar</option>
                      {vendedores.data?.map((v:any)=><option key={v.id} value={v.id}>{v.nombre}</option>)}
                    </select>
                  </Field>
                  <Field label="Zona">
                    <select className={selectCls} value={form.zona_id||''} onChange={e=>set('zona_id',Number(e.target.value))}>
                      <option value="">Sin zona</option>
                      {zonas.data?.map((z:any)=><option key={z.id} value={z.id}>{z.nombre}</option>)}
                    </select>
                  </Field>
                </div>
                <div className="grid grid-cols-2 gap-3">
                  <Field label="Crédito máximo (€)"><input type="number" className={inputCls} min="0" step="100" value={form.credito_maximo||0} onChange={e=>set('credito_maximo',Number(e.target.value))}/></Field>
                  <Field label="IBAN (cobros domiciliados)"><input className={inputCls} value={form.iban||''} onChange={e=>set('iban',e.target.value)} placeholder="ES00 0000 0000 00 0000000000"/></Field>
                </div>
              </div>
              <Field label="Observaciones"><textarea className={inputCls} rows={2} value={form.observaciones||''} onChange={e=>set('observaciones',e.target.value)}/></Field>
            </div>
            <div className="flex gap-2 justify-end px-6 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(false)}>Cancelar</Btn>
              <Btn onClick={save} loading={createMut.isPending||updateMut.isPending}>Guardar cliente</Btn>
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog open={!!delId} title="Eliminar cliente" message="¿Desactivar este cliente? No se eliminan sus datos históricos."
        onConfirm={()=>{deleteMut.mutate(delId!,{onSuccess:()=>setDelId(null)})}}
        onClose={()=>setDelId(null)}/>
    </div>
  )
}
