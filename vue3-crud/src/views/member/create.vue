<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";

const router = useRouter();

const name = ref("");
const email = ref("");
const joined_at = ref(""); // untuk tanggal bergabung
const is_active = ref(true); // untuk toggle aktif (boolean)
const errors = ref({});
const preferences = ref({
  instagram: "",
  facebook: "",
  phone: ""
});

const storeMember = async () => {
  errors.value = {};
  try {
    await api.post("/api/members", {
      name: name.value,
      email: email.value,
      joined_at: joined_at.value || null,
      is_active: is_active.value ? 1 : 0,
      preferences: JSON.stringify(preferences.value),
    });
    router.push({ path: "/member/index" });
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors;
    }
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <form @submit.prevent="storeMember">
              <!-- Nama -->
              <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="name"
                  placeholder="Nama lengkap"
                />
                <div v-if="errors.name" class="alert alert-danger mt-2">
                  {{ errors.name[0] }}
                </div>
              </div>

              <!-- Email -->
              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input
                  type="email"
                  class="form-control"
                  v-model="email"
                  placeholder="Email aktif"
                />
                <div v-if="errors.email" class="alert alert-danger mt-2">
                  {{ errors.email[0] }}
                </div>
              </div>

              <!-- Joined At (tanggal bergabung) -->
              <div class="mb-3">
                <label for="joinedAt" class="form-label fw-bold"
                  >Tanggal Bergabung</label
                >
                <input
                  type="date"
                  id="joinedAt"
                  class="form-control"
                  v-model="joined_at"
                  style="width: 20%"
                />
              </div>
              <!-- Instagram -->
              <div class="mb-3">
                <label class="form-label fw-bold">Instagram</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.instagram"
                  placeholder="Username Instagram"
                />
              </div>

              <!-- Facebook -->
              <div class="mb-3">
                <label class="form-label fw-bold">Facebook</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.facebook"
                  placeholder="Username Facebook"
                />
              </div>

              <!-- No HP -->
              <div class="mb-3">
                <label class="form-label fw-bold">No HP</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="preferences.phone"
                  placeholder="Nomor Handphone"
                />
              </div>

              <!-- Is Active toggle -->
              <div class="mb-3">
                <label class="form-label fw-bold" for="isActiveToggle"
                  >Status Aktif</label
                >
                <div class="form-check form-switch mb-3">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="isActiveToggle"
                    v-model="is_active"
                  />
                </div>
              </div>
              <button
                type="submit"
                class="btn btn-primary rounded-sm shadow border-0"
              >
                Simpan
              </button>
              <button
                class="btn btn-secondary rounded-sm ml-2"
                @click="router.push({ path: '/member/index' })"
              >
                Kembali
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Perbesar toggle switch */
.form-check-input {
  width: 50px; /* Lebar switch */
  height: 24px; /* Tinggi switch */
  cursor: pointer;
}

/* Buat bulatan switch lebih besar */
.form-check-input:checked {
  background-color: #0d6efd; /* warna aktif */
  border-color: #0d6efd;
}

/* Sesuaikan posisi lingkaran bulatan toggle */
.form-check-input:focus {
  box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 0.25);
}

.form-check-input::before {
  width: 14px;
  height: 14px;
  top: 0;
  left: 0;
  border-radius: 17px;
  background-color: white;
  transition: transform 0.3s ease-in-out;
}

/* Pindahkan bulatan ke kanan saat checked */
.form-check-input:checked::before {
  transform: translateX(26px);
}
</style>
