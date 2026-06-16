import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { toast } from 'sonner'
import {
	authApi, dashboardApi, maestrosApi,
	clientesApi, proveedoresApi, articulosApi,
	almacenApi, fabricacionApi, ventasApi, cobrosApi, adminApi,
} from '@/api'

// ── Helpers ────────────────────────────────────────────────────────────────
const onError = (err: any) => toast.error(err?.response?.data?.message ?? 'Error inesperado')

// ── MAESTROS (cacheados largo tiempo) ─────────────────────────────────────
export const useMaestros = () => ({
	formasPago: useQuery({ queryKey: ['formas-pago'], queryFn: maestrosApi.formasPago, staleTime: Infinity }),
	tiposIva: useQuery({ queryKey: ['tipos-iva'], queryFn: maestrosApi.tiposIva, staleTime: Infinity }),
	almacenes: useQuery({ queryKey: ['almacenes'], queryFn: maestrosApi.almacenes, staleTime: Infinity }),
	vendedores: useQuery({ queryKey: ['vendedores'], queryFn: maestrosApi.vendedores, staleTime: Infinity }),
	zonas: useQuery({ queryKey: ['zonas'], queryFn: maestrosApi.zonas, staleTime: Infinity }),
	sectores: useQuery({ queryKey: ['sectores'], queryFn: maestrosApi.sectores, staleTime: Infinity }),
	familias: useQuery({ queryKey: ['familias'], queryFn: maestrosApi.familias, staleTime: Infinity }),
	empresa: useQuery({ queryKey: ['empresa'], queryFn: maestrosApi.empresa, staleTime: Infinity }),
	proveedores: useQuery({
		queryKey: ['proveedores'],
		queryFn: maestrosApi.proveedores,
		staleTime: Infinity
	}),
})

// ── DASHBOARD ──────────────────────────────────────────────────────────────
export const useDashboard = () =>
	useQuery({ queryKey: ['dashboard'], queryFn: dashboardApi.get, staleTime: 30_000, refetchInterval: 60_000 })

// ── CLIENTES ───────────────────────────────────────────────────────────────
export const useClientes = (params?: object) =>
	useQuery({ queryKey: ['clientes', params], queryFn: () => clientesApi.list(params) })

export const useCliente = (id: number) =>
	useQuery({ queryKey: ['clientes', id], queryFn: () => clientesApi.get(id), enabled: id > 0 })

export const useCreateCliente = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: clientesApi.create,
		onSuccess: () => { qc.invalidateQueries({ queryKey: ['clientes'] }); toast.success('Cliente creado') },
		onError,
	})
}
export const useUpdateCliente = (id: number) => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: (data: object) => clientesApi.update(id, data),
		onSuccess: () => { qc.invalidateQueries({ queryKey: ['clientes'] }); toast.success('Cliente actualizado') },
		onError,
	})
}
export const useDeleteCliente = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: clientesApi.delete,
		onSuccess: () => { qc.invalidateQueries({ queryKey: ['clientes'] }); toast.success('Cliente desactivado') },
		onError,
	})
}

// ── PROVEEDORES ────────────────────────────────────────────────────────────
export const useProveedores = (params?: object) =>
	useQuery({ queryKey: ['proveedores', params], queryFn: () => proveedoresApi.list(params) })

export const useCreateProveedor = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: proveedoresApi.create, onSuccess: () => { qc.invalidateQueries({ queryKey: ['proveedores'] }); toast.success('Proveedor creado') }, onError })
}
export const useUpdateProveedor = (id: number) => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: (d: object) => proveedoresApi.update(id, d), onSuccess: () => { qc.invalidateQueries({ queryKey: ['proveedores'] }); toast.success('Proveedor actualizado') }, onError })
}

// ── ARTÍCULOS ──────────────────────────────────────────────────────────────
export const useArticulos = (params?: object) =>
	useQuery({ queryKey: ['articulos', params], queryFn: () => articulosApi.list(params) })

export const useArticulo = (id: number) =>
	useQuery({ queryKey: ['articulos', id], queryFn: () => articulosApi.get(id), enabled: id > 0 })

export const useCreateArticulo = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: articulosApi.create, onSuccess: () => { qc.invalidateQueries({ queryKey: ['articulos'] }); toast.success('Artículo creado') }, onError })
}
export const useUpdateArticulo = (id: number) => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: (d: object) => articulosApi.update(id, d), onSuccess: () => { qc.invalidateQueries({ queryKey: ['articulos'] }); toast.success('Artículo actualizado') }, onError })
}

// ── ALMACÉN ────────────────────────────────────────────────────────────────
export const useStockAlmacen = (params?: object) =>
	useQuery({ queryKey: ['stock', params], queryFn: () => almacenApi.stock(params) })

export const useMovimientos = (params?: object) =>
	useQuery({ queryKey: ['movimientos', params], queryFn: () => almacenApi.movimientos(params) })

export const useRegistrarMovimiento = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: almacenApi.registrar,
		onSuccess: () => { qc.invalidateQueries({ queryKey: ['stock'] }); qc.invalidateQueries({ queryKey: ['movimientos'] }); qc.invalidateQueries({ queryKey: ['articulos'] }); toast.success('Movimiento registrado') },
		onError,
	})
}

export const useInventario = (params?: object) =>
	useQuery({ queryKey: ['inventario', params], queryFn: () => almacenApi.inventario(params) })

// ── FABRICACIÓN ────────────────────────────────────────────────────────────
export const useFormulas = (params?: object) =>
	useQuery({ queryKey: ['formulas', params], queryFn: () => fabricacionApi.formulas(params) })

export const useFormula = (id: number) =>
	useQuery({ queryKey: ['formulas', id], queryFn: () => fabricacionApi.getFormula(id), enabled: id > 0 })

export const useVerificarStock = (id: number, lotes: number) =>
	useQuery({ queryKey: ['verificar-stock', id, lotes], queryFn: () => fabricacionApi.verificarStock(id, lotes), enabled: id > 0 && lotes > 0 })

export const useCreateFormula = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: fabricacionApi.createFormula, onSuccess: () => { qc.invalidateQueries({ queryKey: ['formulas'] }); toast.success('Fórmula creada') }, onError })
}
export const useUpdateFormula = (id: number) => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: (d: object) => fabricacionApi.updateFormula(id, d), onSuccess: () => { qc.invalidateQueries({ queryKey: ['formulas', id] }); toast.success('Fórmula actualizada') }, onError })
}

export const useOrdenesFabricacion = (params?: object) =>
	useQuery({ queryKey: ['ordenes-fab', params], queryFn: () => fabricacionApi.ordenes(params) })

export const useCrearOrden = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: fabricacionApi.crearOrden, onSuccess: () => { qc.invalidateQueries({ queryKey: ['ordenes-fab'] }); toast.success('Orden creada') }, onError })
}
export const useEjecutarFabricacion = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: (id: number) => fabricacionApi.ejecutar(id),
		onSuccess: () => {
			qc.invalidateQueries({ queryKey: ['ordenes-fab'] })
			qc.invalidateQueries({ queryKey: ['stock'] })
			qc.invalidateQueries({ queryKey: ['articulos'] })
			qc.invalidateQueries({ queryKey: ['dashboard'] })
			toast.success('Fabricación ejecutada correctamente')
		},
		onError,
	})
}
export const useAnularOrden = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: ({ id, motivo }: { id: number, motivo?: string }) => fabricacionApi.anularOrden(id, motivo), onSuccess: () => { qc.invalidateQueries({ queryKey: ['ordenes-fab'] }); toast.success('Orden anulada') }, onError })
}

// ── VENTAS ─────────────────────────────────────────────────────────────────
export const useVentas = (params?: object) =>
	useQuery({ queryKey: ['ventas', params], queryFn: () => ventasApi.list(params) })

export const useVenta = (id: number) =>
	useQuery({ queryKey: ['ventas', id], queryFn: () => ventasApi.get(id), enabled: id > 0 })

export const useCreateVenta = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: ventasApi.create,
		onSuccess: (data) => {
			qc.invalidateQueries({ queryKey: ['ventas'] })
			qc.invalidateQueries({ queryKey: ['stock'] })
			qc.invalidateQueries({ queryKey: ['cobros'] })
			qc.invalidateQueries({ queryKey: ['dashboard'] })
			toast.success(`${data.tipo === 'factura' ? 'Factura' : 'Documento'} ${data.numero} creado`)
		},
		onError,
	})
}
export const useAnularVenta = () => {
	const qc = useQueryClient()
	return useMutation({ mutationFn: ventasApi.anular, onSuccess: () => { qc.invalidateQueries({ queryKey: ['ventas'] }); qc.invalidateQueries({ queryKey: ['cobros'] }); toast.success('Documento anulado') }, onError })
}

// ── COBROS ─────────────────────────────────────────────────────────────────
export const useCobros = (params?: object) =>
	useQuery({ queryKey: ['cobros', params], queryFn: () => cobrosApi.list(params) })

export const useCobrar = () => {
	const qc = useQueryClient()
	return useMutation({
		mutationFn: ({ id, ...data }: { id: number } & object) => cobrosApi.cobrar(id, data),
		onSuccess: () => { qc.invalidateQueries({ queryKey: ['cobros'] }); qc.invalidateQueries({ queryKey: ['clientes'] }); qc.invalidateQueries({ queryKey: ['dashboard'] }); toast.success('Cobro registrado') },
		onError,
	})
}
