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

    <!-- Branch Selection -->
<div class="branch-selection-wrapper">
  <div class="branch-selection-container">
    <div class="selection-header">
      <div class="icon-wrapper">
        <i class="bi bi-building"></i>
      </div>
      <h2 class="section-title">Select Your Branch</h2>
      <p class="section-subtitle">Choose a location to view available tables</p>
    </div>
    
    <div class="branch-selector">
      <select
        v-model="selectedBranchId"
        class="branch-select"
        @change="onBranchChange"
      >
        <option value="" disabled>Choose a branch...</option>
        <option
          v-for="branch in branches"
          :key="branch.id"
          :value="branch.id"
        >
          {{ branch.name }}
        </option>
      </select>
      <div class="select-icon-wrapper">
        <i class="bi bi-chevron-down select-icon"></i>
      </div>
    </div>

    <!-- Optional: Selected branch indicator -->
    <div v-if="showBranchIndicator" class="selected-indicator">
      <i class="bi bi-check-circle-fill"></i>
      <span>Branch selected</span>
    </div>
  </div>
</div>

    <!-- Tables Grid -->
    <div class="tables-section">
      <h2 class="section-title">Available Tables</h2>
      <div v-if="selectedBranchId" class="tables-content">
        <div class="tables-grid" v-if="filteredTables.length > 0">
          <div
            v-for="table in filteredTables"
            :key="table.id"
            class="table-card"
            :class="{
              selected: selectedTableId === table.id,
              reserved: isTableReserved(table) || table.status === 'reserved',
              'out-of-service': table.status === 'out_of_service'
            }"
            @click="selectTable(table)"
          >
            <div class="table-icon">
              <i class="bi bi-circle-fill table-indicator" :class="{ reserved: isTableReserved(table) || table.status === 'reserved', 'out-of-service': table.status === 'out_of_service' }"></i>
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
              <span v-if="isTableReserved(table) || table.status === 'reserved'" class="status-badge reserved">
                <i class="bi bi-lock-fill"></i>
                Reserved
              </span>
              <span v-else-if="table.status === 'out_of_service'" class="status-badge out-of-service">
                <i class="bi bi-lock-fill"></i>
                Out of Service
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
        <div v-else class="no-tables-message">
          <i class="bi bi-info-circle"></i>
          <p>No tables available at this branch.</p>
        </div>
      </div>
      <div v-else class="no-branch-message">
        <i class="bi bi-info-circle"></i>
        <p>Please select a branch to view available tables.</p>
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
import { getBranches, type BranchDTO } from '@/api/branches'
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
const branches = ref<BranchDTO[]>([])
const selectedBranchId = ref<number | null>(null)
const selectedTableId = ref<number | null>(null)
const showReservationModal = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const showBranchIndicator = ref(false)

const selectedTable = computed(() =>
  tables.value.find(t => t.id === selectedTableId.value)
)

const filteredTables = computed(() =>
  selectedBranchId.value
    ? tables.value.filter(table => table.branchId === selectedBranchId.value)
    : []
)

onMounted(async () => {
  try {
    tables.value = await getTables()
    branches.value = await getBranches()
    reservations.value = await getReservations()

    // Set up real-time updates every 30 seconds
    setInterval(async () => {
      try {
        const updatedReservations = await getReservations()
        reservations.value = updatedReservations
      } catch (error) {
        console.error('Failed to update reservations:', error)
      }
    }, 30000) // 30 seconds
  } catch (error) {
    console.error(error)
    tables.value = []
    branches.value = []
    reservations.value = []
  }
})

function isTableReserved(table: TableDTO): boolean {
   const now = new Date()
   return reservations.value.some(res =>
     res.tableId === table.id &&
     (res.status === 'confirmed' || res.status === 'pending') &&
     new Date(res.startAt) <= now &&
     new Date(res.startAt).getTime() + 2 * 60 * 60 * 1000 > now.getTime()
   )
 }

function onBranchChange() {
  selectedTableId.value = null
  showBranchIndicator.value = true
  setTimeout(() => {
    showBranchIndicator.value = false
  }, 2000)
}

function selectTable(table: TableDTO) {
  if (isTableReserved(table) || table.status === 'reserved' || table.status === 'out_of_service') return
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
  closeModal()
  errorMessage.value = ''
  successMessage.value = ''

  if (!authStore.isAuthenticated) {
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
      status: 'pending',
      notes: `Guests: ${guests.value}`,
    })

    // Refresh reservations immediately after successful creation
    reservations.value = await getReservations()

    successMessage.value = 'Reservation created successfully! We look forward to serving you.'

    setTimeout(() => {
      successMessage.value = ''
    }, 5000)
  } catch (error: unknown) {
    console.error('Reservation creation error:', error)
    const err = error as { response?: { data?: { error?: string; message?: string }; status?: number } }
    errorMessage.value = err.response?.data?.error || err.response?.data?.message || 'Failed to create reservation'

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

/* Branch Selection */
/* Branch Selection Wrapper - Centers content */
.branch-selection-wrapper {
  min-height: 50vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  margin-bottom: 3rem;
}

.branch-selection-container {
  width: 100%;
  max-width: 500px;
  background: white;
  border-radius: 20px;
  padding: 3rem 2.5rem;
  box-shadow: 
    0 4px 6px rgba(26, 40, 69, 0.05),
    0 10px 20px rgba(26, 40, 69, 0.08);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.branch-selection-container:hover {
  transform: translateY(-2px);
  box-shadow: 
    0 6px 12px rgba(26, 40, 69, 0.08),
    0 15px 30px rgba(26, 40, 69, 0.12);
}

/* Header Section */
.selection-header {
  text-align: center;
  margin-bottom: 2rem;
}

.icon-wrapper {
  width: 64px;
  height: 64px;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  transition: transform 0.3s ease;
}

.icon-wrapper i {
  font-size: 1.75rem;
  color: #8C6353;
}

.branch-selection-container:hover .icon-wrapper {
  transform: scale(1.05);
}

.section-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1A2845;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.02em;
}

.section-subtitle {
  font-size: 0.95rem;
  color: #8C6353;
  margin: 0;
  font-weight: 400;
}

/* Branch Selector */
.branch-selector {
  position: relative;
  margin-bottom: 1.5rem;
}

.branch-select {
  width: 100%;
  padding: 1.25rem 3.5rem 1.25rem 1.25rem;
  border: 2px solid #EEEAE4;
  border-radius: 14px;
  font-size: 1.05rem;
  background: #FEFEFE;
  color: #1A2845;
  cursor: pointer;
  appearance: none;
  transition: all 0.3s ease;
  outline: none;
  font-weight: 500;
}

.branch-select:hover {
  border-color: #E7D7C9;
  background: white;
}

.branch-select:focus {
  border-color: #8C6353;
  background: white;
  box-shadow: 0 0 0 4px rgba(140, 99, 83, 0.12);
}

.branch-select option {
  padding: 1rem;
  font-weight: 500;
}

.branch-select option:disabled {
  color: #8C6353;
  opacity: 0.7;
}

/* Select Icon */
.select-icon-wrapper {
  position: absolute;
  right: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  width: 32px;
  height: 32px;
  background: #EEEAE4;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.branch-select:focus ~ .select-icon-wrapper {
  background: #8C6353;
}

.select-icon {
  color: #8C6353;
  font-size: 0.9rem;
  font-weight: 600;
  transition: all 0.3s ease;
}

.branch-select:focus ~ .select-icon-wrapper .select-icon {
  color: white;
  transform: translateY(2px);
}

/* Selected Indicator */
.selected-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #E7D7C9 0%, #EEEAE4 100%);
  border-radius: 10px;
  color: #8C6353;
  font-size: 0.9rem;
  font-weight: 600;
  animation: slideIn 0.4s ease;
}

.selected-indicator i {
  font-size: 1rem;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .branch-selection-wrapper {
    padding: 1.5rem;
    min-height: 40vh;
  }

  .branch-selection-container {
    padding: 2rem 1.5rem;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .icon-wrapper {
    width: 56px;
    height: 56px;
  }

  .icon-wrapper i {
    font-size: 1.5rem;
  }

  .branch-select {
    padding: 1.1rem 3rem 1.1rem 1.1rem;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .section-title {
    font-size: 1.35rem;
  }

  .section-subtitle {
    font-size: 0.9rem;
  }
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

.table-card.out-of-service {
  opacity: 0.6;
  cursor: not-allowed;
  background: #f5f5f5;

}

.table-card.reserved:hover {
  transform: none;
  box-shadow: none;
}

.table-card.out-of-service:hover {
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

.table-indicator.reserved {
  color: #f44336;
}

.table-indicator.out-of-service {
  color: #ffc107;
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

.status-badge.out-of-service {
  background: #fff3cd;
  color: #856404;
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

/* No Tables/Branch Messages */
.no-tables-message,
.no-branch-message {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 2rem;
  text-align: center;
  color: #8C6353;
}

.no-tables-message i,
.no-branch-message i {
  font-size: 3rem;
  margin-bottom: 1rem;
  opacity: 0.7;
}

.no-tables-message p,
.no-branch-message p {
  font-size: 1.1rem;
  margin: 0;
  font-weight: 500;
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