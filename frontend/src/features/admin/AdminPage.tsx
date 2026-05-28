import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { adminApi, maestrosApi } from '@/api'
import { PageHeader, Card, DataTable, Btn, Field, inputCls, selectCls, Tabs } from '@/components/shared'
import { fmtDatetime } from '@/utils'
import { Plus, X } from 'lucide-react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'

export default function AdminPage() {
  const [tab, setTab] = useState('users')

  const { data: users, isLoading: loadU } = useQuery({ queryKey:['admin-users'], queryFn: adminApi.users })
  const { data: roles }  = useQuery({ queryKey:['roles'], queryFn: adminApi.roles })
  const { data: logs, isLoading: loadL }  = useQuery({ queryKey:['audit-logs'], queryFn: ()=>adminApi.auditLogs({ per_page:50 }) })
  const { data: empresa } = useQuery({ queryKey:['empresa'], queryFn: maestrosApi.empresa })

  const [modal, setModal] = useState(false)
  const [form, setForm]   = useState<any>({ role:'comercial', activo:true })
  const [empForm, setEmpForm] = useState<any>(null)
  const set = (k:string,v:any) => setForm((f:any)=>({...f,[k]:v}))

  const qc = useQueryClient()
  const createUser = useMutation({
    mutationFn: adminApi.createUser,
    onSuccess: ()=>{ qc.invalidateQueries({queryKey:['admin-users']}); toast.success('Usuario creado'); setModal(false) },
    onError: (e:any) => toast.error(e?.response?.data?.message??'Error'),
  })
  const toggleUser = useMutation({
    mutationFn: ({id,activo}:{id:number,activo:boolean})=>adminApi.updateUser(id,{activo}),
    onSuccess: ()=>qc.invalidateQueries({queryKey:['admin-users']}),
  })
  const updateEmpresa = useMutation({
    mutationFn: (d:object)=>maestrosApi.updateEmpresa(d),
    onSuccess: ()=>{ qc.invalidateQueries({queryKey:['empresa']}); toast.success('Configuración guardada') },
  })

  const ROLES_LIST = ['superadmin','admin','comercial','almacenero','contabilidad','solo_lectura']

  const userCols = [
    { key:'name',  label:'Nombre',  render:(r:any)=><span className="font-medium">{r.name}</span> },
    { key:'email', label:'Email',   render:(r:any)=><span className="font-mono text-[12px]">{r.email}</span> },
    { key:'roles', label:'Rol',     render:(r:any)=><span className="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-purple-100 text-purple-800 border border-purple-300">{r.roles?.[0]?.name??'—'}</span> },
    { key:'activo',label:'Estado',  render:(r:any)=><span className={`inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border ${r.activo?'bg-green-100 text-green-800 border-green-300':'bg-red-100 text-red-800 border-red-300'}`}>{r.activo?'Activo':'Inactivo'}</span> },
    { key:'ultimo_acceso', label:'Último acceso', render:(r:any)=>fmtDatetime(r.ultimo_acceso) },
    { key:'_', label:'', render:(r:any)=>(
      <Btn variant="ghost" size="sm" onClick={()=>toggleUser.mutate({id:r.id,activo:!r.activo})}>{r.activo?'Desactivar':'Activar'}</Btn>
    )},
  ]

  const logCols = [
    { key:'created_at', label:'Fecha',  render:(r:any)=>fmtDatetime(r.created_at) },
    { key:'user',       label:'Usuario',render:(r:any)=>r.user?.name??'Sistema' },
    { key:'event',      label:'Evento', render:(r:any)=><span className="font-mono text-[11px]">{r.event}</span> },
    { key:'auditable_type', label:'Entidad', render:(r:any)=>r.auditable_type?.split('\\').pop()??'—' },
    { key:'ip_address', label:'IP',     render:(r:any)=><span className="font-mono text-[12px]">{r.ip_address??'—'}</span> },
  ]

  // Empresa form init
  const emp = empForm ?? empresa

  return (
    <div>
      <PageHeader title="Administración" sub="Usuarios, roles, empresa y auditoría" actions={
        tab==='users' && <Btn onClick={()=>setModal(true)}><Plus size={14}/>Nuevo usuario</Btn>
      }/>

      <Tabs tabs={[{id:'users',label:'Usuarios'},{id:'empresa',label:'Empresa'},{id:'roles',label:'Roles y permisos'},{id:'logs',label:'Auditoría'}]} active={tab} onChange={setTab}/>

      {tab==='users' && <Card noPad><DataTable cols={userCols} rows={users??[]} loading={loadU} keyFn={r=>r.id}/></Card>}

      {tab==='empresa' && (
        <Card className="max-w-2xl">
          {emp && (<>
            <div className="grid grid-cols-2 gap-3">
              <Field label="Nombre / Razón social"><input className={inputCls} value={emp.nombre||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),nombre:e.target.value}))}/></Field>
              <Field label="NIF"><input className={inputCls} value={emp.nif||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),nif:e.target.value}))}/></Field>
            </div>
            <Field label="Dirección"><input className={inputCls} value={emp.direccion||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),direccion:e.target.value}))}/></Field>
            <div className="grid grid-cols-3 gap-3">
              <Field label="CP"><input className={inputCls} value={emp.codigo_postal||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),codigo_postal:e.target.value}))}/></Field>
              <Field label="Localidad"><input className={inputCls} value={emp.localidad||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),localidad:e.target.value}))}/></Field>
              <Field label="Provincia"><input className={inputCls} value={emp.provincia||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),provincia:e.target.value}))}/></Field>
            </div>
            <div className="grid grid-cols-2 gap-3">
              <Field label="Teléfono"><input className={inputCls} value={emp.telefono||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),telefono:e.target.value}))}/></Field>
              <Field label="Email"><input className={inputCls} value={emp.email||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),email:e.target.value}))}/></Field>
            </div>
            <Field label="IBAN bancario"><input className={inputCls} value={emp.iban||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),iban:e.target.value}))}/></Field>
            <div className="grid grid-cols-2 gap-3">
              <Field label="Serie facturas"><input className={inputCls} value={emp.serie_factura||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),serie_factura:e.target.value}))}/></Field>
              <Field label="Pie de factura"><input className={inputCls} value={emp.pie_factura||''} onChange={e=>setEmpForm((f:any)=>({...(f??emp),pie_factura:e.target.value}))}/></Field>
            </div>
            <Btn onClick={()=>updateEmpresa.mutate(empForm??emp)} loading={updateEmpresa.isPending}>Guardar configuración</Btn>
          </>)}
        </Card>
      )}

      {tab==='roles' && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {(roles??[]).map((r:any)=>(
            <Card key={r.id}>
              <div className="text-[14px] font-semibold mb-3 capitalize">{r.name}</div>
              <div className="flex flex-wrap gap-1.5">
                {r.permissions?.map((p:any)=>(
                  <span key={p.id} className="px-2 py-0.5 bg-[#F0EFE9] text-[#6B6860] text-[10px] rounded font-mono">{p.name}</span>
                ))}
              </div>
            </Card>
          ))}
        </div>
      )}

      {tab==='logs' && <Card noPad><DataTable cols={logCols} rows={logs?.data??[]} loading={loadL} keyFn={r=>r.id}/></Card>}

      {/* Modal crear usuario */}
      {modal && (
        <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-4 backdrop-blur-sm" onClick={e=>e.target===e.currentTarget&&setModal(false)}>
          <div className="bg-white rounded-[14px] shadow-xl w-full max-w-sm">
            <div className="flex items-center justify-between px-5 py-4 border-b border-[#E5E3DC]">
              <h3 className="text-[16px] font-semibold">Nuevo usuario</h3>
              <button onClick={()=>setModal(false)} className="p-1.5 rounded hover:bg-[#F0EFE9] text-[#6B6860]"><X size={15}/></button>
            </div>
            <div className="p-5 space-y-3">
              <Field label="Nombre *"><input className={inputCls} value={form.name||''} onChange={e=>set('name',e.target.value)}/></Field>
              <Field label="Email *"><input type="email" className={inputCls} value={form.email||''} onChange={e=>set('email',e.target.value)}/></Field>
              <Field label="Contraseña *"><input type="password" className={inputCls} value={form.password||''} onChange={e=>set('password',e.target.value)}/></Field>
              <Field label="Rol">
                <select className={selectCls} value={form.role} onChange={e=>set('role',e.target.value)}>
                  {ROLES_LIST.map(r=><option key={r} value={r}>{r}</option>)}
                </select>
              </Field>
            </div>
            <div className="flex gap-2 justify-end px-5 pb-5 border-t border-[#E5E3DC] pt-4">
              <Btn variant="secondary" onClick={()=>setModal(false)}>Cancelar</Btn>
              <Btn onClick={()=>createUser.mutate(form)} loading={createUser.isPending}>Crear usuario</Btn>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
