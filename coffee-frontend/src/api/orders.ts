import { api } from './client'

export interface OrderDTO {
  id: number
  userId: number
  branchId: number
  totalAmount: number
  status: 'pending' | 'paid' | 'cancelled'
  paymentMethod?: 'cash' | 'online'
  meta?: object
}

interface OrderRaw {
  id: number
  user_id: number
  branch_id: number
  ord_total_amount: number
  ord_status: 'pending' | 'paid' | 'cancelled'
  ord_payment_method?: 'cash' | 'online'
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
        meta: order.meta
      }));
    }
  });
}
export function getOrder(id: number) {
  return api.get<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; meta?: object }>(`/orders/${id}`)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      meta: r.data.meta
    }))
}
export function createOrder(data: Pick<OrderDTO, 'userId' | 'branchId' | 'totalAmount' | 'status' | 'paymentMethod' | 'meta'>) {
  const apiPayload = {
    user_id: data.userId,
    branch_id: data.branchId,
    ord_total_amount: data.totalAmount,
    ord_status: data.status,
    ord_payment_method: data.paymentMethod,
    meta: data.meta
  }
  return api.post<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; meta?: object }>('/orders', apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      meta: r.data.meta
    }))
}
export function updateOrder(id: number, data: Partial<Pick<OrderDTO, 'userId' | 'branchId' | 'totalAmount' | 'status' | 'paymentMethod' | 'meta'>>) {
  const apiPayload: Record<string, string | number | object | undefined> = {}
  if (data.userId !== undefined) apiPayload.user_id = data.userId
  if (data.branchId !== undefined) apiPayload.branch_id = data.branchId
  if (data.totalAmount !== undefined) apiPayload.ord_total_amount = data.totalAmount
  if (data.status !== undefined) apiPayload.ord_status = data.status
  if (data.paymentMethod !== undefined) apiPayload.ord_payment_method = data.paymentMethod
  if (data.meta !== undefined) apiPayload.meta = data.meta
  return api.put<{ id: number; user_id: number; branch_id: number; ord_total_amount: number; ord_status: 'pending' | 'paid' | 'cancelled'; ord_payment_method?: 'cash' | 'online'; meta?: object }>(`/orders/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      userId: r.data.user_id,
      branchId: r.data.branch_id,
      totalAmount: r.data.ord_total_amount,
      status: r.data.ord_status,
      paymentMethod: r.data.ord_payment_method,
      meta: r.data.meta
    }))
}
export function deleteOrder(id: number) {
  return api.delete(`/orders/${id}`)
}
