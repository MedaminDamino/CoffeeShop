import { api } from './client'

export interface PromotionDTO {
  id: number
  code: string
  description?: string
  discountType: 'percent' | 'fixed'
  discountValue: number
  startDate?: string
  endDate?: string
  usageLimit?: number
  isActive: boolean
}

export function getPromotions() {
  return api.get<{ id: number; code_promo: string; promo_description?: string; promo_discount_type: 'percent' | 'fixed'; promo_discount_value: number; promo_start_date?: string; promo_end_date?: string; promo_usage_limit?: number; promo_is_active: boolean }[]>('/promotions')
    .then((r) => r.data.map(p => ({
      id: p.id,
      code: p.code_promo,
      description: p.promo_description,
      discountType: p.promo_discount_type,
      discountValue: p.promo_discount_value,
      startDate: p.promo_start_date,
      endDate: p.promo_end_date,
      usageLimit: p.promo_usage_limit,
      isActive: p.promo_is_active
    })))
}
export function createPromotion(data: Pick<PromotionDTO, 'code' | 'description' | 'discountType' | 'discountValue' | 'startDate' | 'endDate' | 'usageLimit' | 'isActive'>) {
  const apiPayload = {
    code_promo: data.code,
    promo_description: data.description,
    promo_discount_type: data.discountType,
    promo_discount_value: data.discountValue,
    promo_start_date: data.startDate,
    promo_end_date: data.endDate,
    promo_usage_limit: data.usageLimit,
    promo_is_active: data.isActive
  }
  return api.post<{ id: number; code_promo: string; promo_description?: string; promo_discount_type: 'percent' | 'fixed'; promo_discount_value: number; promo_start_date?: string; promo_end_date?: string; promo_usage_limit?: number; promo_is_active: boolean }>('/promotions', apiPayload)
    .then((r) => ({
      id: r.data.id,
      code: r.data.code_promo,
      description: r.data.promo_description,
      discountType: r.data.promo_discount_type,
      discountValue: r.data.promo_discount_value,
      startDate: r.data.promo_start_date,
      endDate: r.data.promo_end_date,
      usageLimit: r.data.promo_usage_limit,
      isActive: r.data.promo_is_active
    }))
}
export function deletePromotion(id: number) {
  return api.delete(`/promotions/${id}`)
}

export function usePromotion(code: string) {
  return api.post('/promotion-usages', { code }).then((r) => r.data)
}
