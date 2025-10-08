<template>
  <NavBar />
  
  <div class="reservation-container">
    <!-- Header -->
    <div class="reservation-header">
      <h1 class="page-title">Reserve Your Table</h1>
      <p class="page-subtitle">Select a table and choose your preferred time</p>
    </div>

    <!-- Success/Error Messages -->
    <transition name="fade">
      <div v-if="successMessage" class="alert-message success">
        <i class="bi bi-check-circle-fill"></i>
        <span>{{ successMessage }}</span>
      </div>
    </transition>
    
    <transition name="fade">
      <div v-if="errorMessage" class="alert-message error">
        <i class="bi bi-exclamation-circle-fill"></i>
        <span>{{ errorMessage }}</span>
      </div>
    </transition>

    <!-- Tables Grid -->
    <div class="tables-section">
      <h2 class="section-title">Available Tables</h2>
      <div class="tables-grid">
        <div
          v-for="table in tables"
          :key="table.id"
          class="table-card"
          :class="{ 
            selected: selectedTableId === table.id,
            reserved: isTableReserved(table)
          }"
          @click="selectTable(table)"
        >
          <div class="table-icon">
            <i class="bi bi-circle-fill table-indicator"></i>
            <span class="table-number">{{ table.number }}</span>
          </div>
          <div class="table-info">
            <h3 class="table-name">Table {{ table.number }}</h3>
            <p class="table-capacity">
              <i class="bi bi-people-fill"></i>
              {{ table.capacity }} seats
            </p>
          </div>
          <div class="table-status">
            <span v-if="isTableReserved(table)" class="status-badge reserved">
              <i class="bi bi-lock-fill"></i>
              Reserved
            </span>
            <span v-else class="status-badge available">
              <i class="bi bi-check-circle-fill"></i>
              Available
            </span>
          </div>
          <div class="selection-indicator">
            <i class="bi bi-check-lg"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Reservation Modal -->
  <teleport to="body">
    <transition name="modal">
      <div v-if="showReservationModal" class="modal-overlay" @click="closeModal">
        <div class="reservation-modal" @click.stop>
          <button class="modal-close" @click="closeModal">
            <i class="bi bi-x-lg"></i>
          </button>
          
          <div class="modal-header">
            <div class="modal-icon">
              <i class="bi bi-calendar-check"></i>
            </div>
            <h2 class="modal-title">Complete Your Reservation</h2>
            <p class="modal-subtitle">Table {{ selectedTable?.number }} - {{ selectedTable?.capacity }} seats</p>
          </div>

          <form @submit.prevent="submitReservation" class="reservation-form">
            <div class="form-group-row">
              <div class="form-group">
                <label class="form-label">
                  <i class="bi bi-calendar3"></i>
                  Date
                </label>
                <input
                  v-model="date"
                  type="date"
                  class="form-input"
                  :min="new Date().toISOString().split('T')[0]"
                  required
                />
              </div>

              <div class="form-group">
                <label class="form-label">
                  <i class="bi bi-clock"></i>
                  Time
                </label>
                <input
                  v-model="time"
                  type="time"
                  class="form-input"
                  required
                />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">
                <i class="bi bi-people"></i>
                Number of Guests
              </label>
              <div class="guests-selector">
                <button
                  type="button"
                  class="guest-btn"
                  @click="decrementGuests"
                  :disabled="guests <= 1"
                >
                  <i class="bi bi-dash-lg"></i>
                </button>
                <span class="guest-count">{{ guests }}</span>
                <button
                  type="button"
                  class="guest-btn"
                  @click="incrementGuests"
                  :disabled="guests >= (selectedTable?.capacity || 10)"
                >
                  <i class="bi bi-plus-lg"></i>
                </button>
              </div>
              <p class="guest-helper">Maximum {{ selectedTable?.capacity }} guests for this table</p>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeModal">
                Cancel
              </button>
              <button type="submit" class="btn-primary">
                <i class="bi bi-check-lg"></i>
                Confirm Reservation
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>
  </teleport>

  <AppFooter />
</template>

<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { getTables, type TableDTO } from '@/api/tables'
import { getReservations, createReservation, type ReservationDTO } from '@/api/reservations'
import NavBar from '@/components/NavBar.vue'
import AppFooter from '@/components/AppFooter.vue'
import { useAuthStore } from '@/stores/auth'
import { Modal } from 'bootstrap'

const authStore = useAuthStore()
const date = ref('')
const time = ref('')
const guests = ref(2)
const tables = ref<TableDTO[]>([])
const reservations = ref<ReservationDTO[]>([])
const selectedTableId = ref<number | null>(null)
const showReservationModal = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const selectedTable = computed(() => 
  tables.value.find(t => t.id === selectedTableId.value)
)

onMounted(async () => {
  try {
    tables.value = await getTables()
    reservations.value = await getReservations()
  } catch (error) {
    console.error(error)
    tables.value = []
    reservations.value = []
  }
})

function isTableReserved(table: TableDTO): boolean {
  const now = new Date()
  return reservations.value.some(res =>
    res.tableId === table.id &&
    res.status === 'confirmed' &&
    new Date(res.startAt) <= now &&
    new Date(res.startAt).getTime() + 2 * 60 * 60 * 1000 > now.getTime()
  )
}

function selectTable(table: TableDTO) {
  if (isTableReserved(table)) return
  selectedTableId.value = table.id
  showReservationModal.value = true
}

function closeModal() {
  showReservationModal.value = false
}

function incrementGuests() {
  if (guests.value < (selectedTable.value?.capacity || 10)) {
    guests.value++
  }
}

function decrementGuests() {
  if (guests.value > 1) {
    guests.value--
  }
}

async function submitReservation() {
  errorMessage.value = ''
  successMessage.value = ''
  
  if (!authStore.isAuthenticated) {
    closeModal()
    const modal = new Modal(document.getElementById('authModal')!)
    modal.show()
    return
  }

  if (!selectedTableId.value || !date.value || !time.value) return

  const startAt = `${date.value} ${time.value}:00`
  
  try {
    await createReservation({
      userId: authStore.user!.id,
      tableId: selectedTableId.value,
      startAt,
      status: 'confirmed',
      notes: `Guests: ${guests.value}`,
    })
    
    successMessage.value = 'Reservation created successfully! We look forward to serving you.'
    closeModal()
    
    setTimeout(() => {
      successMessage.value = ''
    }, 5000)
  } catch (error: unknown) {
    const err = error as { response?: { data?: { error?: string } } }
    errorMessage.value = err.response?.data?.error || 'Failed to create reservation'
    
    setTimeout(() => {
      errorMessage.value = ''
    }, 5000)
  }
}
</script>

<style scoped>
.reservation-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 6rem 1.5rem 4rem;
  min-height: calc(100vh - 200px);
}

/* Header */
.reservation-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.5px;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #8C6353;
  margin: 0;
}

/* Alert Messages */
.alert-message {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-radius: 16px;
  margin-bottom: 2rem;
  font-size: 1rem;
  font-weight: 500;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.alert-message.success {
  background: #d4edda;
  color: #155724;
  border: 2px solid #c3e6cb;
}

.alert-message.error {
  background: #f8d7da;
  color: #721c24;
  border: 2px solid #f5c6cb;
}

.alert-message i {
  font-size: 1.5rem;
}

.fade-enter-active, .fade-leave-active {
  transition: all 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Tables Section */
.tables-section {
  margin-bottom: 3rem;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1A2845;
  margin-bottom: 1.5rem;
  text-align: center;
}

.tables-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.table-card {
  background: #FFFFFF;
  border: 2px solid #EEEAE4;
  border-radius: 20px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.table-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(231, 215, 201, 0.1) 0%, rgba(140, 99, 83, 0.05) 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.table-card:hover::before {
  opacity: 1;
}

.table-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 28px rgba(26, 40, 69, 0.15);
  border-color: #E7D7C9;
}

.table-card.selected {
  border-color: #1A2845;
  background: linear-gradient(135deg, rgba(26, 40, 69, 0.05) 0%, rgba(231, 215, 201, 0.1) 100%);
}

.table-card.reserved {
  opacity: 0.6;
  cursor: not-allowed;
  background: #f5f5f5;
}

.table-card.reserved:hover {
  transform: none;
  box-shadow: none;
}

.table-icon {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 1rem;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.table-indicator {
  position: absolute;
  top: 8px;
  right: 8px;
  font-size: 0.8rem;
  color: #4caf50;
}

.table-card.reserved .table-indicator {
  color: #f44336;
}

.table-number {
  font-size: 2rem;
  font-weight: 800;
  color: #1A2845;
}

.table-info {
  text-align: center;
  margin-bottom: 1rem;
}

.table-name {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0 0 0.5rem 0;
}

.table-capacity {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  color: #8C6353;
  margin: 0;
}

.table-capacity i {
  font-size: 1.1rem;
}

.table-status {
  display: flex;
  justify-content: center;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-badge.available {
  background: #d4edda;
  color: #155724;
}

.status-badge.reserved {
  background: #f8d7da;
  color: #721c24;
}

.selection-indicator {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 32px;
  height: 32px;
  background: #1A2845;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  opacity: 0;
  transform: scale(0);
  transition: all 0.3s ease;
}

.table-card.selected .selection-indicator {
  opacity: 1;
  transform: scale(1);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(26, 40, 69, 0.7);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
  padding: 1rem;
}

.reservation-modal {
  background: #FFFFFF;
  border-radius: 24px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  position: relative;
  box-shadow: 0 20px 60px rgba(26, 40, 69, 0.3);
}

.modal-close {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  width: 40px;
  height: 40px;
  border: none;
  background: #EEEAE4;
  color: #1A2845;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  z-index: 1;
}

.modal-close:hover {
  background: #E7D7C9;
  transform: rotate(90deg);
}

.modal-header {
  text-align: center;
  padding: 3rem 2rem 2rem;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  border-radius: 24px 24px 0 0;
}

.modal-icon {
  width: 80px;
  height: 80px;
  background: #1A2845;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  font-size: 2rem;
}

.modal-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0 0 0.5rem 0;
}

.modal-subtitle {
  font-size: 1rem;
  color: #8C6353;
  margin: 0;
  font-weight: 500;
}

.reservation-form {
  padding: 2rem;
}

.form-group-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #1A2845;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
}

.form-label i {
  color: #8C6353;
}

.form-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border: 2px solid #EEEAE4;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  outline: none;
  background: white;
}

.form-input:focus {
  border-color: #8C6353;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.1);
}

.guests-selector {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  padding: 1rem;
  background: #EEEAE4;
  border-radius: 16px;
}

.guest-btn {
  width: 48px;
  height: 48px;
  border: none;
  background: #1A2845;
  color: white;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  transition: all 0.3s ease;
}

.guest-btn:hover:not(:disabled) {
  background: #8C6353;
  transform: scale(1.1);
}

.guest-btn:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.guest-count {
  font-size: 2rem;
  font-weight: 700;
  color: #1A2845;
  min-width: 60px;
  text-align: center;
}

.guest-helper {
  text-align: center;
  font-size: 0.85rem;
  color: #8C6353;
  margin: 0.75rem 0 0 0;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
}

.btn-secondary,
.btn-primary {
  flex: 1;
  padding: 1rem;
  border: none;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-secondary {
  background: #EEEAE4;
  color: #1A2845;
}

.btn-secondary:hover {
  background: #E7D7C9;
}

.btn-primary {
  background: #1A2845;
  color: white;
}

.btn-primary:hover {
  background: #8C6353;
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(26, 40, 69, 0.2);
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s ease;
}

.modal-enter-from, .modal-leave-to {
  opacity: 0;
}

.modal-enter-from .reservation-modal,
.modal-leave-to .reservation-modal {
  transform: scale(0.9) translateY(20px);
}

/* Responsive */
@media (max-width: 768px) {
  .reservation-container {
    padding: 5rem 1rem 3rem;
  }

  .page-title {
    font-size: 2rem;
  }

  .tables-grid {
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 1rem;
  }

  .form-group-row {
    grid-template-columns: 1fr;
  }

  .reservation-modal {
    border-radius: 20px;
  }

  .modal-header {
    padding: 2.5rem 1.5rem 1.5rem;
  }

  .reservation-form {
    padding: 1.5rem;
  }

  .form-actions {
    flex-direction: column;
  }
}
</style>