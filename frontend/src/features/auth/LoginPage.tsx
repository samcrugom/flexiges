import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { useAuthStore } from '@/store/authStore'
import { authApi } from '@/api'
import { toast } from 'sonner'
import { Loader2 } from 'lucide-react'

export default function LoginPage() {
  const [email, setEmail] = useState('admin@efgnext.es')
  const [pass, setPass]   = useState('password')
  const [loading, setLoading] = useState(false)
  const { setAuth } = useAuthStore()
  const navigate = useNavigate()

  const submit = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    try {
      const data = await authApi.login(email, pass)
      setAuth(data.token, data.user)
      navigate('/dashboard')
    } catch (err: any) {
      toast.error(err?.response?.data?.message ?? 'Credenciales incorrectas')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="min-h-screen bg-[#F7F6F3] flex items-center justify-center p-4">
      <div className="w-full max-w-sm">
        <div className="text-center mb-8">
          <h1 className="text-[28px] font-semibold tracking-tight text-[#1A1916]">FlexiGES</h1>
          <p className="text-[13px] text-[#6B6860] mt-1">Gestión Comercial — Acceso</p>
        </div>
        <div className="bg-white border border-[#E5E3DC] rounded-[14px] shadow-sm p-6">
          <form onSubmit={submit} className="space-y-4">
            <div>
              <label className="block text-[12px] font-medium text-[#6B6860] mb-1.5">Email</label>
              <input type="email" value={email} onChange={e=>setEmail(e.target.value)} required autoFocus
                className="w-full px-3 py-2 border border-[#E5E3DC] rounded-[7px] text-[13px] outline-none focus:border-[#52B788] focus:ring-2 focus:ring-[#52B788]/10"/>
            </div>
            <div>
              <label className="block text-[12px] font-medium text-[#6B6860] mb-1.5">Contraseña</label>
              <input type="password" value={pass} onChange={e=>setPass(e.target.value)} required
                className="w-full px-3 py-2 border border-[#E5E3DC] rounded-[7px] text-[13px] outline-none focus:border-[#52B788] focus:ring-2 focus:ring-[#52B788]/10"/>
            </div>
            <button type="submit" disabled={loading}
              className="w-full py-2.5 bg-[#2D6A4F] text-white rounded-[7px] text-[13px] font-medium flex items-center justify-center gap-2 hover:bg-[#235740] transition-colors disabled:opacity-60">
              {loading && <Loader2 size={14} className="animate-spin"/>}
              Entrar
            </button>
          </form>
        </div>
        <p className="text-center text-[11px] text-[#6B6860] mt-4">FlexiGES Next v1.0 · React 19 + Laravel 12</p>
      </div>
    </div>
  )
}
