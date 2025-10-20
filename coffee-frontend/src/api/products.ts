import type { Product } from '@/interfaces/Product'
import { api } from './client'

interface ProductRaw {
  id: number
  prod_name: string
  prod_price: number
  prod_description?: string
  prod_image_url?: string
  prod_is_active: boolean
  prod_meta?: object
  category_id: number
}

export async function getProducts(params?: { page?: number; per_page?: number; show_all?: string; sort_by?: string; sort_direction?: 'asc' | 'desc' }) {
  const response = await api.get('/products', { params })
  const data = response.data;

  if (data && typeof data === 'object' && 'data' in data) {
    // Paginated response
    return {
      ...data,
      data: data.data.map((product: ProductRaw) => ({
        id: product.id,
        name: product.prod_name,
        price: product.prod_price,
        description: product.prod_description,
        imageUrl: product.prod_image_url,
        isActive: product.prod_is_active,
        meta: product.prod_meta,
        categoryId: product.category_id
      }))
    };
  } else {
    // Regular array response
    return data.map((product: ProductRaw) => ({
      id: product.id,
      name: product.prod_name,
      price: product.prod_price,
      description: product.prod_description,
      imageUrl: product.prod_image_url,
      isActive: product.prod_is_active,
      meta: product.prod_meta,
      categoryId: product.category_id
    }));
  }
}

export async function createProduct(product: Pick<Product, 'name' | 'price' | 'description' | 'imageUrl' | 'isActive' | 'meta' | 'categoryId'>) {
  const apiPayload = {
    prod_name: product.name,
    prod_price: product.price,
    prod_description: product.description,
    prod_image_url: product.imageUrl,
    prod_is_active: product.isActive,
    prod_meta: product.meta,
    category_id: product.categoryId
  }
  const response = await api.post<{ id: number; prod_name: string; prod_price: number; prod_description?: string; prod_image_url?: string; prod_is_active: boolean; prod_meta?: object; category_id: number }>('/products', apiPayload)
  return {
    id: response.data.id,
    name: response.data.prod_name,
    price: response.data.prod_price,
    description: response.data.prod_description,
    imageUrl: response.data.prod_image_url,
    isActive: response.data.prod_is_active,
    meta: response.data.prod_meta,
    categoryId: response.data.category_id
  }
}

export async function updateProduct(id: number, product: Partial<Pick<Product, 'name' | 'price' | 'description' | 'imageUrl' | 'isActive' | 'meta' | 'categoryId'>>) {
  const apiPayload: Record<string, string | number | boolean | object | undefined> = {}
  if (product.name !== undefined) apiPayload.prod_name = product.name
  if (product.price !== undefined) apiPayload.prod_price = product.price
  if (product.description !== undefined) apiPayload.prod_description = product.description
  if (product.imageUrl !== undefined) apiPayload.prod_image_url = product.imageUrl
  if (product.isActive !== undefined) apiPayload.prod_is_active = product.isActive
  if (product.meta !== undefined) apiPayload.prod_meta = product.meta
  if (product.categoryId !== undefined) apiPayload.category_id = product.categoryId

  const response = await api.put<{ id: number; prod_name: string; prod_price: number; prod_description?: string; prod_image_url?: string; prod_is_active: boolean; prod_meta?: object; category_id: number }>(`/products/${id}`, apiPayload)
  return {
    id: response.data.id,
    name: response.data.prod_name,
    price: response.data.prod_price,
    description: response.data.prod_description,
    imageUrl: response.data.prod_image_url,
    isActive: response.data.prod_is_active,
    meta: response.data.prod_meta,
    categoryId: response.data.category_id
  }
}

export async function deleteProduct(id: number) {
  await api.delete(`/products/${id}`)
}
