<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";
import FlatPickr from "vue-flatpickr-component";
import "flatpickr/dist/flatpickr.css";
import { Indonesian } from "flatpickr/dist/l10n/id.js";

const loans = ref([]);
const isLoading = ref(true);
const router = useRouter();

const searchQuery = ref("");
const selectedReturnStatus = ref(""); // "", "returned", "notReturned"
const dateRange = ref({ start: null, end: null });

const fetchDataLoans = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/loans");
    loans.value = response.data.data;
  } catch (error) {
    console.error("Gagal fetch data loans:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  if (!token) {
    router.push({ name: "auth.login" });
  } else {
    fetchDataLoans();
  }
});

const returnLoan = async (loan) => {
  try {
    await api.put(`/api/loans/${loan.id}`, {
      member_id: loan.member_id,
      book_id: loan.book_id,
      returned_at: new Date().toISOString()
    });
    fetchDataLoans();
    Swal.fire("Success", "Buku berhasil dikembalikan!", "success");
  } catch (error) {
    console.error("Gagal update pengembalian:", error);
    Swal.fire("Error", "Gagal mengembalikan buku", "error");
  }
};

const confirmReturn = (loan) => {
  Swal.fire({
    title: "Yakin ingin mengembalikan buku?",
    text: "Status pinjaman akan diperbarui.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, kembalikan",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      returnLoan(loan);
    }
  });
};

const confirmDelete = (loan) => {
  Swal.fire({
    title: "Yakin ingin menghapus data pinjaman?",
    text: "Data yang sudah dihapus tidak bisa dikembalikan!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#aaa",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/api/loans/${loan.id}`);
        Swal.fire("Deleted!", "Data pinjaman berhasil dihapus.", "success");
        fetchDataLoans();
      } catch (error) {
        Swal.fire("Error", "Gagal menghapus data pinjaman.", "error");
      }
    }
  });
};
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
  const day = tanggal.getDate().toString().padStart(2, "0"); // tambahkan leading zero
  const month = bulan[tanggal.getMonth()];
  const year = tanggal.getFullYear();

  return `${day} ${month} ${year}`;
}

const filteredLoans = computed(() => {
  return loans.value.filter((loan) => {
    // Search nama anggota & judul buku
    const searchLower = searchQuery.value.toLowerCase();
    const matchesSearch = loan.member.name.toLowerCase().includes(searchLower);
    // ||
    // loan.book.title.toLowerCase().includes(searchLower)
    // Filter tanggal peminjaman (tanggal sama dengan selectedBorrowDate)
    let matchDate = true;
    if (dateRange.value.start && dateRange.value.end) {
      const joined = new Date(loan.borrowed_at).toISOString().slice(0, 10); // hanya YYYY-MM-DD
      const start = dateRange.value.start;
      const end = dateRange.value.end;
      matchDate = joined >= start && joined <= end;
    }

    // Filter status pengembalian
    let matchesReturnStatus = true;
    if (selectedReturnStatus.value === "returned") {
      matchesReturnStatus = !!loan.returned_at;
    } else if (selectedReturnStatus.value === "notReturned") {
      matchesReturnStatus = !loan.returned_at;
    }

    return matchesSearch && matchDate && matchesReturnStatus;
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
    const response = await api.get("/api/loans/export", {
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
    const response = await api.post("/api/loans/import", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });
    fetchDataLoans();
  } catch (error) {
    console.error("Gagal Import:", error);
    Swal.fire("Error", "Gagal melakukan import data", "error");
  }
};
</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row mb-3">
      <div class="col-9">
        <router-link
          :to="{ name: 'loan.create' }"
          class="btn btn-md btn-success rounded shadow border-0 mb-3 me-2"
        >
          Tambah Peminjaman
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
          <input type="file" accept=".xlsx,.xls" @change="importExcel" hidden />
        </label>
      </div>
      <div class="col-md-3 text-end">
        <input
          type="text"
          v-model="searchQuery"
          class="form-control"
          placeholder="Cari nama anggota"
        />
      </div>
    </div>
    <!-- Tabel -->
    <div class="card border-0 rounded shadow">
      <div class="card-body">
        <table class="table table-bordered table-hover align-middle">
          <thead class="bg-dark text-white">
            <tr>
              <th>Nama Anggota</th>
              <th>Judul Buku</th>
              <th
                class="d-flex align-items-center gap-2 justify-content-between"
              >
                <span>Tanggal Peminjaman</span>
                <div
                  class="position-relative"
                  style="width: 30px; height: 30px"
                >
                  <i
                    class="bi bi-calendar3 text-white cursor-pointer position-absolute top-50 start-50 translate-middle"
                    style="font-size: 20px"
                  ></i>
                  <flat-pickr
                    v-model="selectedBorrowDate"
                    :config="dateConfig"
                    class="position-absolute bg-transparent border-0 outline-none start-0"
                  />
                </div>
              </th>
              <th>
                <div class="d-flex row align-items-center">
                  <div class="col-11">Tanggal Pengembalian</div>
                  <div class="select-wrapper col-1">
                    <select v-model="selectedReturnStatus" class="form-select">
                      <option value="">Semua Status Pengembalian</option>
                      <option value="returned">Sudah Dikembalikan</option>
                      <option value="notReturned">Belum Dikembalikan</option>
                    </select>
                    <i class="bi bi-filter-square text-white"></i>
                  </div>
                </div>
              </th>
              <th style="width: 25%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="6" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredLoans.length === 0">
              <td colspan="6" class="text-center">
                <div class="alert alert-danger mb-0">
                  Data Peminjaman Belum Tersedia!
                </div>
              </td>
            </tr>
            <tr v-else v-for="loan in filteredLoans" :key="loan.id">
              <td>{{ loan.member.name }}</td>
              <td>{{ loan.book.title }}</td>
              <td>{{ formatTanggal(loan.borrowed_at) }}</td>
              <td>
                <span v-if="loan.returned_at">{{
                  formatTanggal(loan.returned_at)
                }}</span>
                <span v-else class="text-danger">Belum Dikembalikan</span>
              </td>
              <td>
                <button
                  v-if="!loan.returned_at"
                  @click.prevent="confirmReturn(loan)"
                  class="btn btn-warning btn-sm me-2"
                  title="Kembalikan Buku"
                >
                  <i class="bi bi-box-arrow-in-left"></i>
                </button>
                <router-link
                  :to="{ name: 'loan.detail', params: { id: loan.id } }"
                  class="btn btn-sm btn-warning rounded-sm shadow border-0 me-2"
                  title="Detail"
                >
                  <i class="bi bi-info-lg"></i>
                </router-link>
                <button
                  @click.prevent="confirmDelete(loan)"
                  class="btn btn-sm btn-danger rounded-sm shadow border-0"
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

.select-wrapper select:focus {
  outline: none;
  border: none;
  box-shadow: none;
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
