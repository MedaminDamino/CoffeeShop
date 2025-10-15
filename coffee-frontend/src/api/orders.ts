import { api } from './client'

export interface OrderDTO {
  id: number
  userId: number
  branchId: number
  totalAmount: number
  status: 'pending' | 'paid' | 'cancelled'
  paymentMethod?: 'cash' | 'online'
  products?: { product_id: number; quantity: number; price: number }[]
  meta?: object
}

interface OrderRaw {
  id: number
  user_id: number
  branch_id: number
  ord_total_amount: number
  ord_status: 'pending' | 'paid' | 'cancelled'
  ord_payment_method?: 'cash' | 'online'
  products?: { product_id: number; quantity: number; price: number }[]
  meta?: object
}

export function getOrders(params?: { page?: number; per_page?: number }) {
  return api.get('/orders', { params }).then((r) => {
    const data = r.data;
    if (data && typeof data === 'object' && 'data' in data) {
      // Paginated response
      return {
        ...data,
        data: data.data.map((order: OrderRaw) => ({
          id: order.id,
          userId: order.user_id,
          branchId: order.branch_id,
          totalAmount: order.ord_total_amount,
          status: order.ord_status,
          paymentMethod: order.ord_payment_method,
          products: order.products,
          meta: order.meta
        }))
      };
    } else {
      // Regular array response
      return data.map((order: OrderRaw) => ({
        id: order.id,
        userId: order.user_id,
        branchId: order.branch_id,
        totalAmount: order.ord_total_amount,
        status: order.ord_status,
        paymentMethod: order.ord_payment_method,
        products: order.products,
        meta: order.meta
      }));
    }
  });
}
export function getOrder(id: number) {
  return api.get<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; products?: { product_id: number; quantity: number; price: number }[]; meta?: object }>(`/orders/${id}`)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      products: r.data.products,
      meta: r.data.meta
    }))
}
export function createOrder(data: Pick<OrderDTO, 'userId' | 'branchId' | 'totalAmount' | 'status' | 'paymentMethod' | 'products' | 'meta'>) {
  const apiPayload = {
    user_id: data.userId,
    branch_id: data.branchId,
    ord_total_amount: data.totalAmount,
    ord_status: data.status,
    ord_payment_method: data.paymentMethod,
    products: data.products,
    meta: data.meta
  }
  return api.post<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; products?: { product_id: number; quantity: number; price: number }[]; meta?: object }>('/orders', apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      products: r.data.products,
      meta: r.data.meta
    }))
}
export function updateOrder(id: number, data: Record<string, unknown>) {
  const apiPayload: Record<string, string | number | object | undefined> = {}
  if (data.userId !== undefined && data.userId !== null) apiPayload.user_id = data.userId as string | number
  if (data.branchId !== undefined && data.branchId !== null) apiPayload.branch_id = data.branchId as string | number
  if (data.totalAmount !== undefined && data.totalAmount !== null) apiPayload.ord_total_amount = data.totalAmount as string | number
  if (data.status !== undefined && data.status !== null) apiPayload.ord_status = data.status as string
  if (data.paymentMethod !== undefined && data.paymentMethod !== null) apiPayload.ord_payment_method = data.paymentMethod as string
  if (data.products !== undefined && data.products !== null) apiPayload.products = data.products as object
  if (data.meta !== undefined && data.meta !== null) apiPayload.meta = data.meta as object

  // Handle direct API field names for backend compatibility
  if (data.user_id !== undefined && data.user_id !== null) apiPayload.user_id = data.user_id as string | number
  if (data.branch_id !== undefined && data.branch_id !== null) apiPayload.branch_id = data.branch_id as string | number
  if (data.ord_total_amount !== undefined && data.ord_total_amount !== null) apiPayload.ord_total_amount = data.ord_total_amount as string | number
  if (data.ord_status !== undefined && data.ord_status !== null) apiPayload.ord_status = data.ord_status as string
  if (data.ord_payment_method !== undefined && data.ord_payment_method !== null) apiPayload.ord_payment_method = data.ord_payment_method as string

  return api.put<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; products?: { product_id: number; quantity: number; price: number }[]; meta?: object }>(`/orders/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      products: r.data.products,
      meta: r.data.meta
    }))
}
export function addItemToOrder(data: { userId: number; branchId: number; productId: number; quantity: number }) {
  return api.post('/orders/add-item', {
    user_id: data.userId,
    branch_id: data.branchId,
    product_id: data.productId,
    quantity: data.quantity,
  })
}

export function deleteOrder(id: number) {
  return api.delete(`/orders/${id}`)
}
