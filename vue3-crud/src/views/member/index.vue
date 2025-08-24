<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";
import FlatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Indonesian } from "flatpickr/dist/l10n/id.js";

const members = ref([]);
const isLoading = ref(true);
const router = useRouter();

// state untuk search & filter
const searchQuery = ref("");
const dateRange = ref({ start: null, end: null });
const statusFilter = ref("");

const fetchDataMembers = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/members");
    members.value = response.data;
  } catch (error) {
    console.error("Gagal fetch data members:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  if (!token) {
    router.push({ name: "auth.login" });
  } else {
    fetchDataMembers();
  }
});

// fungsi delete
const deleteMember = async (id) => {
  try {
    await api.delete(`/api/members/${id}`);
    fetchDataMembers();
  } catch (error) {
    console.error("Gagal hapus member:", error);
  }
};

const confirmDelete = (id) => {
  Swal.fire({
    title: "Yakin ingin menghapus member?",
    text: "Data tidak bisa dikembalikan setelah dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      deleteMember(id);
      Swal.fire("Terhapus!", "Member berhasil dihapus.", "success");
    }
  });
};

// format tanggal
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

// computed untuk filter data
const filteredMembers = computed(() => {
  return members.value
    .filter((member) => {
      // search filter
      const keyword = searchQuery.value.toLowerCase();
      const matchSearch =
        member.name.toLowerCase().includes(keyword) ||
        member.email.toLowerCase().includes(keyword);

      // status filter
      const matchStatus =
        statusFilter.value === "" ||
        String(Number(member.is_active)) === statusFilter.value;

      // date range filter
      let matchDate = true;
      if (dateRange.value.start && dateRange.value.end) {
        const joined = new Date(member.joined_at).toISOString().slice(0, 10); // hanya YYYY-MM-DD
        const start = dateRange.value.start;
        const end = dateRange.value.end;
        matchDate = joined >= start && joined <= end;
      }

      return matchSearch && matchStatus && matchDate;
    })
    .map((member) => {
      // parsing preferences di sini
      let prefs = { instagram: "", facebook: "", phone: "" };
      if (member.preferences) {
        try {
          prefs =
            typeof member.preferences === "string"
              ? JSON.parse(member.preferences)
              : member.preferences;
        } catch {
          // gagal parse, tetap kosong
        }
      }
      return {
        ...member,
        preferencesParsed: prefs
      };
    });
});

const dateConfig = {
  locale: Indonesian,
  mode: "range",
  dateFormat: "Y-m-d",
  altInput: true,
  altFormat: "d F Y",
  onChange: (selectedDates) => {
    if (selectedDates.length === 2) {
      dateRange.value.start = selectedDates[0].toISOString().slice(0, 10);
      dateRange.value.end = selectedDates[1].toISOString().slice(0, 10);
    } else if (selectedDates.length === 1) {
      const tgl = selectedDates[0].toISOString().slice(0, 10);
      dateRange.value.start = tgl;
      dateRange.value.end = tgl;
    } else {
      dateRange.value.start = null;
      dateRange.value.end = null;
    }
  },
  onReady: (selectedDates, dateStr, instance) => {
    const clearBtn = document.createElement("button");
    clearBtn.type = "button";
    clearBtn.classList.add(
      "btn",
      "btn-sm",
      "btn-primary",
      "mt-2",
      "left-0",
      "end-0"
    );
    clearBtn.innerText = "Bersihkan Pilihan";
    clearBtn.addEventListener("click", () => {
      instance.clear();
      dateRange.value.start = null;
      dateRange.value.end = null;
    });
    instance.calendarContainer.appendChild(clearBtn);
  }
};
const exportExcel = async () => {
  try {
    const response = await api.get("/api/members/export", {
      responseType: "blob"
    });
    Swal.fire("Sukses", response.data.message, "success");
    // const blob = new Blob([response.data], {
    //   type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    // });
    // const link = document.createElement("a");
    // link.href = URL.createObjectURL(blob);
    // link.download = "members.xlsx";
    // link.click();
    // URL.revokeObjectURL(link.href);
  } catch (error) {
    console.error("Gagal Export:", error);
    Swal.fire("Error", "Gagal melakukan export data", "error");
  }
};

const importExcel = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);

  try {
    const response = await api.post("/api/members/import", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });

    const { duplicates } = response.data;

    if (duplicates && duplicates.length > 0) {
      Swal.fire({
        icon: "warning",
        title: "Import belum selesai karena ada email duplikat",
        html: `Email berikut sudah ada dan tidak diimport:<br><b>${duplicates.join(
          ", "
        )}</b>, silahkan periksa kembali`
      });
    } else {
      Swal.fire("Berhasil", "Import data member berhasil!", "success");
    }

    fetchDataMembers();
  } catch (error) {
    console.error("Gagal Import:", error);
    Swal.fire("Error", "Gagal melakukan import data", "error");
  }
};

</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row">
      <div class="row mb-3">
        <div class="col-md-9">
          <router-link
            :to="{ name: 'member.create' }"
            class="btn btn-md btn-success rounded shadow border-0 mb-3 me-2"
          >
            Tambah Anggota
          </router-link>
          <button
            @click="exportExcel"
            class="btn btn-md btn-primary rounded shadow border-0 mb-3 me-2"
          >
            Export
          </button>

          <!-- Tombol Import -->
          <label class="btn btn-md btn-secondary rounded shadow border-0 mb-3">
            Import
            <input
              type="file"
              accept=".xlsx,.xls"
              @change="importExcel"
              hidden
            />
          </label>
        </div>
        <div class="col-md-3 text-end">
          <input
            type="text"
            v-model="searchQuery"
            class="form-control"
            placeholder="Cari nama atau email..."
          />
        </div>
      </div>

      <div class="card border-0 rounded shadow">
        <div class="card-body">
          <table class="table table-bordered">
            <thead class="bg-dark text-white">
              <tr>
                <th>Nama</th>
                <th>Email</th>
                <th
                  class="d-flex align-items-center gap-2 justify-content-between"
                >
                  <span>Bergabung</span>
                  <div
                    class="position-relative"
                    style="width: 30px; height: 30px"
                  >
                    <i
                      class="bi bi-calendar3 text-white cursor-pointer position-absolute top-50 start-50 translate-middle"
                      style="font-size: 20px"
                    ></i>
                    <flat-pickr
                      :config="dateConfig"
                      class="position-absolute bg-transparent border-0 outline-none start-0"
                    />
                  </div>
                </th>
                <th>Sosial Media</th>
                <th>
                  <div class="d-flex row align-items-center">
                    <div class="col-11">Status Aktif</div>
                    <div class="select-wrapper col-1">
                      <select v-model="statusFilter" class="no-text">
                        <option value="" disabled class="hidden"></option>
                        <option value="">Semua Status</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non Aktif</option>
                      </select>
                      <i class="bi bi-filter-square text-white"></i>
                    </div>
                  </div>
                </th>
                <th style="width: 15%">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="isLoading">
                <td colspan="7" class="text-center">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </td>
              </tr>
              <tr v-else-if="filteredMembers.length === 0">
                <td colspan="7" class="text-center">
                  <div class="alert alert-danger mb-0">
                    Data Member Belum Tersedia!
                  </div>
                </td>
              </tr>
              <tr
                v-else
                v-for="(member, index) in filteredMembers"
                :key="member.id"
              >
                <td>{{ member.name }}</td>
                <td>{{ member.email }}</td>
                <td>{{ formatTanggal(member.joined_at) }}</td>
                <td>
                  <div>
                    <strong>IG:</strong>
                    {{ member.preferencesParsed.instagram || "-" }}
                  </div>
                  <div>
                    <strong>FB:</strong>
                    {{ member.preferencesParsed.facebook || "-" }}
                  </div>
                  <div>
                    <strong>HP:</strong>
                    {{ member.preferencesParsed.phone || "-" }}
                  </div>
                </td>
                <td>{{ member.is_active ? "Aktif" : "Non Aktif" }}</td>
                <td class="text-center">
                  <router-link
                    :to="{ name: 'member.edit', params: { id: member.id } }"
                    class="btn btn-sm btn-primary me-2"
                    title="Edit"
                  >
                    <i class="bi bi-pencil"></i>
                  </router-link>
                  <router-link
                    :to="{ name: 'member.detail', params: { id: member.id } }"
                    class="btn btn-sm btn-warning me-2"
                    title="Detail"
                  >
                    <i class="bi bi-info-lg"></i>
                  </router-link>
                  <button
                    @click.prevent="confirmDelete(member.id)"
                    class="btn btn-sm btn-danger"
                    title="Delete"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<style>
.select-wrapper {
  position: relative;
  display: inline-block;
}

.select-wrapper select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 30px;
  background-color: transparent;
  color: black;
  border: none;
  cursor: pointer;
  outline: none;
}

.select-wrapper i {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-size: 20px;
}
</style>
