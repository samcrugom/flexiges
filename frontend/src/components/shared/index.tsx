import { cn, estadoColors, fmtEur, fmtNum } from '@/utils'
import { Search, X, ChevronLeft, ChevronRight, Loader2 } from 'lucide-react'
import { useState } from 'react'

// ── Stat Card ──────────────────────────────────────────────────────────────
interface StatCardProps { label:string; value:string|number; sub?:string; color?:string; onClick?:()=>void }
export function StatCard({ label, value, sub, color, onClick }: StatCardProps) {
  return (
    <div onClick={onClick} className={cn('bg-white border border-[#E5E3DC] rounded-[10px] p-4 shadow-sm', onClick && 'cursor-pointer hover:border-[#2D6A4F] transition-colors')}>
      <div className="text-[11px] font-semibold text-[#6B6860] uppercase tracking-[0.5px]">{label}</div>
      <div className={cn('text-[26px] font-semibold mt-1 tracking-tight', color ?? 'text-[#1A1916]')}>{typeof value === 'number' ? fmtNum(value,0) : value}</div>
      {sub && <div className="text-[12px] text-[#6B6860] mt-0.5">{sub}</div>}
    </div>
  )
}

// ── Estado Badge ───────────────────────────────────────────────────────────
export function EstadoBadge({ estado }: { estado: string }) {
  const cls = estadoColors[estado?.toLowerCase()] ?? 'bg-gray-100 text-gray-600 border-gray-300'
  return (
    <span className={cn('inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium border', cls)}>
      {estado?.charAt(0).toUpperCase() + estado?.slice(1).replace('_',' ')}
    </span>
  )
}

// ── Page Header ────────────────────────────────────────────────────────────
interface PageHeaderProps { title:string; sub?:string; actions?:React.ReactNode }
export function PageHeader({ title, sub, actions }: PageHeaderProps) {
  return (
    <div className="flex items-center justify-between mb-5">
      <div>
        <h1 className="text-[20px] font-semibold tracking-tight text-[#1A1916]">{title}</h1>
        {sub && <p className="text-[13px] text-[#6B6860] mt-0.5">{sub}</p>}
      </div>
      {actions && <div className="flex items-center gap-2">{actions}</div>}
    </div>
  )
}

// ── Search Input ───────────────────────────────────────────────────────────
export function SearchInput({ value, onChange, placeholder = 'Buscar...' }: { value:string; onChange:(v:string)=>void; placeholder?:string }) {
  return (
    <div className="relative max-w-xs flex-1">
      <Search size={14} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-[#6B6860] pointer-events-none" />
      <input
        value={value}
        onChange={e => onChange(e.target.value)}
        placeholder={placeholder}
        className="w-full pl-8 pr-3 py-2 border border-[#E5E3DC] rounded-[7px] text-[13px] bg-white outline-none focus:border-[#52B788] focus:ring-2 focus:ring-[#52B788]/10"
      />
      {value && <button onClick={() => onChange('')} className="absolute right-2 top-1/2 -translate-y-1/2 text-[#6B6860] hover:text-[#1A1916]"><X size={13} /></button>}
    </div>
  )
}

// ── Table ──────────────────────────────────────────────────────────────────
interface Col<T> { key:string; label:string; render?:(row:T)=>React.ReactNode; className?:string; align?:'left'|'right'|'center' }
interface DataTableProps<T> { cols:Col<T>[]; rows:T[]; loading?:boolean; empty?:string; onRowClick?:(row:T)=>void; keyFn?:(row:T)=>string|number }
export function DataTable<T extends Record<string,any>>({ cols, rows, loading, empty='Sin resultados', onRowClick, keyFn }: DataTableProps<T>) {
  return (
    <div className="overflow-x-auto">
      <table className="w-full border-collapse">
        <thead>
          <tr>
            {cols.map(c => (
              <th key={c.key} className={cn('text-left text-[11px] font-semibold text-[#6B6860] uppercase tracking-[0.5px] px-3 py-2.5 border-b-2 border-[#E5E3DC] whitespace-nowrap', c.align === 'right' && 'text-right', c.className)}>
                {c.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {loading && (
            <tr><td colSpan={cols.length} className="py-12 text-center text-[#6B6860]">
              <Loader2 size={20} className="animate-spin mx-auto mb-2" /><div className="text-[13px]">Cargando...</div>
            </td></tr>
          )}
          {!loading && rows.length === 0 && (
            <tr><td colSpan={cols.length} className="py-12 text-center text-[#6B6860] text-[13px]">{empty}</td></tr>
          )}
          {!loading && rows.map((row, i) => (
            <tr key={keyFn ? keyFn(row) : i}
              onClick={() => onRowClick?.(row)}
              className={cn('border-b border-[#E5E3DC] last:border-0 transition-colors', onRowClick && 'cursor-pointer hover:bg-[#F0EFE9]')}>
              {cols.map(c => (
                <td key={c.key} className={cn('px-3 py-[11px] text-[13px]', c.align === 'right' && 'text-right', c.className)}>
                  {c.render ? c.render(row) : row[c.key] ?? '—'}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}

// ── Pagination ─────────────────────────────────────────────────────────────
export function Pagination({ page, lastPage, onChange }: { page:number; lastPage:number; onChange:(p:number)=>void }) {
  if (lastPage <= 1) return null
  return (
    <div className="flex items-center gap-1 justify-end mt-4">
      <button disabled={page===1} onClick={()=>onChange(page-1)} className="w-8 h-8 flex items-center justify-center rounded-[6px] border border-[#E5E3DC] bg-white disabled:opacity-40 hover:bg-[#F0EFE9] transition-colors"><ChevronLeft size={14}/></button>
      {Array.from({length:Math.min(5,lastPage)},(_,i)=>{
        const p = Math.max(1, Math.min(page - 2 + i, lastPage - 4 + i))
        return (
          <button key={p} onClick={()=>onChange(p)} className={cn('w-8 h-8 flex items-center justify-center rounded-[6px] text-[13px] border transition-colors', p===page ? 'bg-[#2D6A4F] text-white border-[#2D6A4F]' : 'border-[#E5E3DC] bg-white hover:bg-[#F0EFE9]')}>{p}</button>
        )
      })}
      <button disabled={page===lastPage} onClick={()=>onChange(page+1)} className="w-8 h-8 flex items-center justify-center rounded-[6px] border border-[#E5E3DC] bg-white disabled:opacity-40 hover:bg-[#F0EFE9] transition-colors"><ChevronRight size={14}/></button>
    </div>
  )
}

// ── Card ───────────────────────────────────────────────────────────────────
export function Card({ children, className, noPad }: { children:React.ReactNode; className?:string; noPad?:boolean }) {
  return <div className={cn('bg-white border border-[#E5E3DC] rounded-[10px] shadow-sm', !noPad && 'p-5', className)}>{children}</div>
}

// ── Form Field ─────────────────────────────────────────────────────────────
export function Field({ label, error, children, className }: { label:string; error?:string; children:React.ReactNode; className?:string }) {
  return (
    <div className={cn('mb-3.5', className)}>
      <label className="block text-[12px] font-medium text-[#6B6860] mb-1.5">{label}</label>
      {children}
      {error && <p className="text-[11px] text-red-600 mt-1">{error}</p>}
    </div>
  )
}

export const inputCls = 'w-full px-3 py-2 border border-[#E5E3DC] rounded-[7px] text-[13px] font-[DM_Sans,sans-serif] bg-white text-[#1A1916] outline-none focus:border-[#52B788] focus:ring-2 focus:ring-[#52B788]/10 transition-colors'
export const selectCls = inputCls

// ── Confirm Modal ──────────────────────────────────────────────────────────
export function ConfirmDialog({ open, title, message, onConfirm, onClose, danger=true }:
  { open:boolean; title:string; message:string; onConfirm:()=>void; onClose:()=>void; danger?:boolean }) {
  if (!open) return null
  return (
    <div className="fixed inset-0 bg-black/35 z-50 flex items-center justify-center p-5 backdrop-blur-sm">
      <div className="bg-white rounded-[14px] shadow-xl w-full max-w-sm animate-in fade-in zoom-in-95 duration-150">
        <div className="p-5">
          <h3 className="text-[16px] font-semibold mb-2">{title}</h3>
          <p className="text-[13px] text-[#6B6860]">{message}</p>
        </div>
        <div className="flex gap-2 justify-end px-5 pb-5">
          <button onClick={onClose} className="px-4 py-2 rounded-[7px] border border-[#E5E3DC] bg-white text-[13px] font-medium hover:bg-[#F0EFE9] transition-colors">Cancelar</button>
          <button onClick={onConfirm} className={cn('px-4 py-2 rounded-[7px] text-[13px] font-medium text-white transition-colors', danger ? 'bg-red-600 hover:bg-red-700' : 'bg-[#2D6A4F] hover:bg-[#235740]')}>Confirmar</button>
        </div>
      </div>
    </div>
  )
}

// ── Tabs ───────────────────────────────────────────────────────────────────
export function Tabs({ tabs, active, onChange }: { tabs:{id:string;label:string}[]; active:string; onChange:(id:string)=>void }) {
  return (
    <div className="flex gap-0.5 border-b-2 border-[#E5E3DC] mb-5">
      {tabs.map(t => (
        <button key={t.id} onClick={() => onChange(t.id)}
          className={cn('px-4 py-2 text-[13px] font-medium transition-all border-b-2 -mb-0.5 rounded-t',
            active===t.id ? 'text-[#2D6A4F] border-[#2D6A4F]' : 'text-[#6B6860] border-transparent hover:text-[#1A1916] hover:bg-[#F0EFE9]')}>
          {t.label}
        </button>
      ))}
    </div>
  )
}

// ── Button ─────────────────────────────────────────────────────────────────
interface BtnProps { children:React.ReactNode; onClick?:()=>void; variant?:'primary'|'secondary'|'danger'|'ghost'; size?:'sm'|'md'; type?:'button'|'submit'; disabled?:boolean; className?:string; loading?:boolean }
export function Btn({ children, onClick, variant='primary', size='md', type='button', disabled, className, loading }: BtnProps) {
  const base = 'inline-flex items-center gap-1.5 font-medium rounded-[7px] transition-all duration-150 border-0 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed'
  const sizes = { sm:'px-2.5 py-1.5 text-[12px]', md:'px-3.5 py-2 text-[13px]' }
  const variants = {
    primary:   'bg-[#2D6A4F] text-white hover:bg-[#235740]',
    secondary: 'bg-white text-[#1A1916] border border-[#E5E3DC] hover:bg-[#F0EFE9]',
    danger:    'bg-red-600 text-white hover:bg-red-700',
    ghost:     'bg-transparent text-[#6B6860] hover:bg-[#F0EFE9] hover:text-[#1A1916]',
  }
  return (
    <button type={type} onClick={onClick} disabled={disabled || loading} className={cn(base, sizes[size], variants[variant], className)}>
      {loading && <Loader2 size={13} className="animate-spin" />}
      {children}
    </button>
  )
}
