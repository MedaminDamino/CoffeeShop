import { api } from './client'

export interface OrderItemDTO {
  id?: number
  order_id: number
  product_id: number
  qty: number
  price?: number
}

export function addOrderItem(data: Omit<OrderItemDTO, 'id'>) {
  return api.post<OrderItemDTO>('/order-items', data).then((r) => r.data)
}
export function removeOrderItem(id: number) {
  return api.delete(`/order-items/${id}`).then((r) => r.data)
}
