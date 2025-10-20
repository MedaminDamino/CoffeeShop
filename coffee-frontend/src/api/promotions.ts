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

interface PromotionRaw {
  id: number
  code_promo: string
  promo_description?: string
  promo_discount_type: 'percent' | 'fixed'
  promo_discount_value: number
  promo_start_date?: string
  promo_end_date?: string
  promo_usage_limit?: number
  promo_is_active: boolean
}

export function getPromotions(params?: { page?: number; per_page?: number }) {
  return api.get('/promotions', { params }).then((r) => {
    const data = r.data;
    if (data && typeof data === 'object' && 'data' in data) {
      // Paginated response
      return {
        ...data,
        data: data.data.map((promotion: PromotionRaw) => ({
          id: promotion.id,
          code: promotion.code_promo,
          description: promotion.promo_description,
          discountType: promotion.promo_discount_type,
          discountValue: promotion.promo_discount_value,
          startDate: promotion.promo_start_date,
          endDate: promotion.promo_end_date,
          usageLimit: promotion.promo_usage_limit,
          isActive: promotion.promo_is_active

        }))
      };
    } else {
      // Regular array response
      return data.map((promotion: PromotionRaw) => ({
        id: promotion.id,
        code: promotion.code_promo,
        description: promotion.promo_description,
        discountType: promotion.promo_discount_type,
        discountValue: promotion.promo_discount_value,
        startDate: promotion.promo_start_date,
        endDate: promotion.promo_end_date,
        usageLimit: promotion.promo_usage_limit,
        isActive: promotion.promo_is_active,

      }));
    }
  });
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
      isActive: r.data.promo_is_active,
    }))
}
export function updatePromotion(id: number, data: Partial<Pick<PromotionDTO, 'code' | 'description' | 'discountType' | 'discountValue' | 'startDate' | 'endDate' | 'usageLimit' | 'isActive'>>) {
  const apiPayload: Record<string, string | number | boolean | undefined> = {}
  if (data.code !== undefined) apiPayload.code_promo = data.code
  if (data.description !== undefined) apiPayload.promo_description = data.description
  if (data.discountType !== undefined) apiPayload.promo_discount_type = data.discountType
  if (data.discountValue !== undefined) apiPayload.promo_discount_value = data.discountValue
  if (data.startDate !== undefined) apiPayload.promo_start_date = data.startDate
  if (data.endDate !== undefined) apiPayload.promo_end_date = data.endDate
  if (data.usageLimit !== undefined) apiPayload.promo_usage_limit = data.usageLimit
  if (data.isActive !== undefined) apiPayload.promo_is_active = data.isActive

  return api.put<{ id: number; code_promo: string; promo_description?: string; promo_discount_type: 'percent' | 'fixed'; promo_discount_value: number; promo_start_date?: string; promo_end_date?: string; promo_usage_limit?: number; promo_is_active: boolean }>(`/promotions/${id}`, apiPayload)
    .then((r) => ({
      id: r.data.id,
      code: r.data.code_promo,
      description: r.data.promo_description,
      discountType: r.data.promo_discount_type,
      discountValue: r.data.promo_discount_value,
      startDate: r.data.promo_start_date,
      endDate: r.data.promo_end_date,
      usageLimit: r.data.promo_usage_limit,
      isActive: r.data.promo_is_active,
 
    }))
}
export function deletePromotion(id: number) {
  return api.delete(`/promotions/${id}`)
}

export function usePromotion(code: string) {
  return api.post('/promotion-usages', { code }).then((r) => r.data)
}

export function validatePromotion(code: string) {
  console.log("Frontend: Sending request to /promotions/validate with payload:", { code_promo: code })
  return api.post('/promotions/validate', { code_promo: code }).then((r) => {
    console.log("Frontend: Received response from backend:", r.data)
    return r.data
  })
}
