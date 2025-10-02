import type { Product } from '@/interfaces/Product'
import { api } from './client'

export async function getProducts() {
  const response = await api.get<{ id: number; prod_name: string; prod_price: number; prod_description?: string; prod_image_url?: string; prod_is_active: boolean; prod_meta?: object; category_id: number }[]>('/products')
  return response.data.map(p => ({
    id: p.id,
    name: p.prod_name,
    price: p.prod_price,
    description: p.prod_description,
    imageUrl: p.prod_image_url,
    isActive: p.prod_is_active,
    meta: p.prod_meta,
    categoryId: p.category_id
  }))
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
