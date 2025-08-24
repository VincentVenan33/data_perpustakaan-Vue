<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const name = ref("");
const email = ref("");
const joinedAt = ref("");
const isActive = ref(false);
const isLoading = ref(true);

const audits = ref([]);
const isAuditLoading = ref(true);
const users = ref([]); // untuk lookup nama user

function formatTanggal(dateString) {
  const bulan = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember"
  ];
  const tanggal = new Date(dateString);
  const day = tanggal.getDate().toString().padStart(2, "0");
  const month = bulan[tanggal.getMonth()];
  const year = tanggal.getFullYear();
  return `${day} ${month} ${year}`;
}

onMounted(async () => {
  try {
    // ambil semua user untuk mapping nama
    const userRes = await api.get("/api/users");
    users.value = userRes.data.data || userRes.data;

    // ambil detail member
    const response = await api.get(`/api/members/${route.params.id}`);
    const data = response.data.data;
    name.value = data.name || "";
    email.value = data.email || "";
    joinedAt.value = formatTanggal(data.joined_at);
    isActive.value = data.is_active;

    await fetchAudits();
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Gagal mengambil data",
      text: "Member tidak ditemukan atau terjadi kesalahan."
    }).then(() => {
      router.push({ path: "/member/index" });
    });
  } finally {
    isLoading.value = false;
  }
});

async function fetchAudits() {
  try {
    isAuditLoading.value = true;
    const res = await api.get(`/api/members/${route.params.id}/audits`);
    audits.value = res.data.data || res.data;
  } catch (error) {
    console.error("Gagal ambil data audit:", error);
  } finally {
    isAuditLoading.value = false;
  }
}

function getUserName(audit) {
  if (audit.user_id) {
    const found = users.value.find((u) => u.id === audit.user_id);
    if (found) return found.name;
  }
  if (audit.user && audit.user.name) return audit.user.name;
  if (audit.user_type) return audit.user_type.split("\\").pop();
  return "System";
}

function mapEvent(event) {
  const map = {
    created: "Menambah",
    updated: "Mengubah",
    deleted: "Menghapus"
  };
  return map[event] || event;
}

const fieldMap = {
  name: "Nama",
  email: "Email",
  joined_at: "Tanggal Bergabung",
  is_active: "Status Aktif"
};

function formatChanges(oldValues, newValues) {
  try {
    const oldObj =
      typeof oldValues === "string" ? JSON.parse(oldValues) : oldValues || {};
    const newObj =
      typeof newValues === "string" ? JSON.parse(newValues) : newValues || {};
    const changes = [];

    for (const key in { ...oldObj, ...newObj }) {
      if (key === "id") continue; // skip id

      if (oldObj[key] !== newObj[key]) {
        const fieldName = fieldMap[key] || key;

        let oldVal = oldObj[key] ?? "-";
        let newVal = newObj[key] ?? "-";

        // ubah status aktif jadi label
        if (key === "is_active") {
          oldVal = oldVal ? "Aktif" : "Tidak Aktif";
          newVal = newVal ? "Aktif" : "Tidak Aktif";
        }

        // ubah tanggal bergabung jadi format tanggal
        if (key === "joined_at") {
          oldVal = oldVal !== "-" ? formatTanggal(oldVal) : "-";
          newVal = newVal !== "-" ? formatTanggal(newVal) : "-";
        }

        changes.push(`${fieldName}: "${oldVal}" → "${newVal}"`);
      }
    }
    return changes.length ? changes.join("\n") : "-";
  } catch {
    return "-";
  }
}
</script>

<template>
  <div class="container mt-4">
    <div class="row">
      <div class="col-md-12">
        <!-- Detail Member -->
        <div class="card border-0 rounded shadow mb-4">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <div v-else>
              <div class="d-flex justify-content-end mb-3">
                <button
                  class="btn btn-secondary rounded-sm shadow border-0"
                  @click="router.push({ path: '/member/index' })"
                >
                  Kembali
                </button>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <p class="form-control-plaintext">{{ name }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <p class="form-control-plaintext">{{ email }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Bergabung</label>
                <p class="form-control-plaintext">{{ joinedAt }}</p>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Status Aktif</label>
                <p class="form-control-plaintext">
                  {{ isActive ? "Aktif" : "Tidak Aktif" }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h2 class="text-2xl font-semibold mb-2 mt-4 text-white">
            Riwayat Perubahan
          </h2>
        </div>
        <div class="card border-0 rounded shadow mb-4">
          <div class="card-body">
            <div v-if="isAuditLoading" class="text-center my-3">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <table v-else class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Event</th>
                  <th>User</th>
                  <th>Perubahan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="audit in audits" :key="audit.id">
                  <td>{{ formatTanggal(audit.created_at) }}</td>
                  <td>{{ mapEvent(audit.event) }}</td>
                  <td>{{ getUserName(audit) }}</td>
                  <td>
                    <pre style="white-space: pre-wrap; margin: 0"
                      >{{ formatChanges(audit.old_values, audit.new_values) }}
                      </pre
                    >
                  </td>
                </tr>
                <tr v-if="audits.length === 0">
                  <td colspan="4" class="text-center">
                    Belum ada riwayat perubahan
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
