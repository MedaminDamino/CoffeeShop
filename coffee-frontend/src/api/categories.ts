import { api } from './client'

export interface CategoryDTO {
  id: number
  name: string
  description?: string
}

export async function getCategories() {
  const response = await api.get<{ id: number; cat_name: string; cat_description?: string }[]>('/categories')
  return response.data.map(cat => ({
    id: cat.id,
    name: cat.cat_name,
    description: cat.cat_description
  }))
}

export async function createCategory(payload: Pick<CategoryDTO, 'name' | 'description'>) {
  const apiPayload = {
    cat_name: payload.name,
    cat_description: payload.description
  }
  const response = await api.post<{ id: number; cat_name: string; cat_description?: string }>('/categories', apiPayload)
  return {
    id: response.data.id,
    name: response.data.cat_name,
    description: response.data.cat_description
  }
}
