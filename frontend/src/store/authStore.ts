import { create } from 'zustand'
import { persist } from 'zustand/middleware'

interface AuthUser {
  id: number
  name: string
  email: string
  roles: string[]
  permissions: string[]
  avatar_path?: string
}

interface AuthState {
  token: string | null
  user: AuthUser | null
  setAuth: (token: string, user: AuthUser) => void
  logout: () => void
  hasPermission: (perm: string) => boolean
  hasRole: (role: string) => boolean
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      token: null,
      user:  null,
      setAuth: (token, user) => set({ token, user }),
      logout:  () => set({ token: null, user: null }),
      hasPermission: (perm) => {
        const u = get().user
        if (!u) return false
        if (u.roles.includes('superadmin')) return true
        return u.permissions.includes(perm)
      },
      hasRole: (role) => get().user?.roles.includes(role) ?? false,
    }),
    { name: 'flexiges-auth', partialize: (s) => ({ token: s.token, user: s.user }) }
  )
)
