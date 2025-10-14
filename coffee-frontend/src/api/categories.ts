import { api } from './client'

export interface CategoryDTO {
  id: number
  name: string
  description?: string
}

interface CategoryRaw {
  id: number
  cat_name: string
  cat_description?: string
}

export async function getCategories(params?: { page?: number; per_page?: number; sort_by?: string; sort_direction?: 'asc' | 'desc' }) {
  const response = await api.get('/categories', { params })
  const data = response.data;

  if (data && typeof data === 'object' && 'data' in data) {
    // Paginated response
    return {
      ...data,
      data: data.data.map((category: CategoryRaw) => ({
        id: category.id,
        name: category.cat_name,
        description: category.cat_description
      }))
    };
  } else {
    // Regular array response
    return data.map((category: CategoryRaw) => ({
      id: category.id,
      name: category.cat_name,
      description: category.cat_description
    }));
  }
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

export async function updateCategory(id: number, payload: Partial<Pick<CategoryDTO, 'name' | 'description'>>) {
  const apiPayload: Record<string, string | undefined> = {}
  if (payload.name !== undefined) apiPayload.cat_name = payload.name
  if (payload.description !== undefined) apiPayload.cat_description = payload.description

  const response = await api.put<{ id: number; cat_name: string; cat_description?: string }>(`/categories/${id}`, apiPayload)
  return {
    id: response.data.id,
    name: response.data.cat_name,
    description: response.data.cat_description
  }
}

export async function deleteCategory(id: number) {
  await api.delete(`/categories/${id}`)
}
