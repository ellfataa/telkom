<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import {
    Edit, Plus, Search, Trash2, X, User, Phone,
    Loader2, UserX, Save
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Interface Data
interface Pelanggan {
    id_pelanggan: number;
    nama_pelanggan: string;
    kontak: string;
    created_at?: string;
}

// State
const pelangganList = ref<Pelanggan[]>([]);
const searchQuery = ref('');
const showModal = ref(false);
const modalMode = ref<'add' | 'edit'>('add');
const isLoading = ref(false);
const isSubmitting = ref(false);

// Form State
const formData = ref({
    id: 0,
    nama_pelanggan: '',
    kontak: ''
});

// 1. Fetch Data
const fetchPelanggan = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('/api/pelanggan');
        pelangganList.value = res.data.data;
    } catch (e) {
        console.error(e);
        alert('Gagal mengambil data pelanggan');
    } finally {
        isLoading.value = false;
    }
};

// 2. Filter Pencarian
const filteredList = computed(() => {
    if (!searchQuery.value) return pelangganList.value;
    const lowerQuery = searchQuery.value.toLowerCase();
    return pelangganList.value.filter(p =>
        p.nama_pelanggan.toLowerCase().includes(lowerQuery) ||
        (p.kontak && p.kontak.includes(lowerQuery))
    );
});

// 3. Modal Actions
const openModal = (mode: 'add' | 'edit', data?: Pelanggan) => {
    modalMode.value = mode;
    if (mode === 'edit' && data) {
        formData.value = {
            id: data.id_pelanggan,
            nama_pelanggan: data.nama_pelanggan,
            kontak: data.kontak
        };
    } else {
        formData.value = { id: 0, nama_pelanggan: '', kontak: '' };
    }
    showModal.value = true;
};

// 4. Simpan Data (Add/Edit)
const saveData = async () => {
    if (!formData.value.nama_pelanggan) return alert('Nama wajib diisi');

    isSubmitting.value = true;
    try {
        if (modalMode.value === 'add') {
            await axios.post('/api/pelanggan', formData.value);
        } else {
            await axios.put(`/api/pelanggan/${formData.value.id}`, formData.value);
        }

        // Tutup modal dan refresh data
        showModal.value = false;
        fetchPelanggan();

        // Optional: Reset form
        formData.value = { id: 0, nama_pelanggan: '', kontak: '' };

    } catch (e: any) {
        console.error(e);
        alert(e.response?.data?.message || 'Gagal menyimpan data');
    } finally {
        isSubmitting.value = false;
    }
};

// 5. Hapus Data
const deleteData = async (id: number) => {
    if(confirm('Yakin ingin menghapus pelanggan ini? Data yang dihapus tidak dapat dikembalikan.')) {
        try {
            await axios.delete(`/api/pelanggan/${id}`);
            fetchPelanggan();
        } catch (e) {
            alert('Gagal menghapus data');
        }
    }
}

// Input Hanya Angka
const ruleInputAngka = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let val = target.value.replace(/\D/g, '');
    target.value = val;
    formData.value.kontak = val;
}

// Get Initials untuk Avatar
const getInitials = (name: string) => {
    return name.charAt(0).toUpperCase();
}

onMounted(fetchPelanggan);
</script>

<template>
    <Sidebar>
        <div class="space-y-6 font-sans">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Pelanggan</h2>
                    <p class="text-sm text-gray-500">Kelola data pelanggan untuk keperluan penawaran.</p>
                </div>
                <button @click="openModal('add')" class="group bg-blue-600 text-white px-4 py-2.5 rounded-xl flex items-center gap-2 hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30 active:scale-95">
                    <Plus class="w-5 h-5 group-hover:rotate-90 transition-transform"/>
                    <span class="font-medium">Tambah Pelanggan</span>
                </button>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col min-h-[60vh]">

                <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center gap-4">
                    <div class="relative w-full max-w-md">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama atau nomor kontak..."
                            class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm"
                        >
                    </div>
                    <div class="hidden sm:block px-3 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium border border-blue-100">
                        {{ filteredList.length }} Pelanggan
                    </div>
                </div>

                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 uppercase text-xs text-gray-500 font-semibold tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4">Nama Pelanggan</th>
                                <th class="px-6 py-4">Kontak (HP/WA)</th>
                                <th class="px-6 py-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="isLoading">
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <Loader2 class="w-8 h-8 text-blue-600 animate-spin" />
                                        <span class="text-gray-500 font-medium">Memuat data...</span>
                                    </div>
                                </td>
                            </tr>

                            <tr v-else-if="filteredList.length === 0">
                                <td colspan="4" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mb-1">
                                            <UserX class="w-6 h-6 text-gray-400" />
                                        </div>
                                        <p class="text-gray-900 font-medium">Data tidak ditemukan</p>
                                        <p class="text-gray-500 text-xs">Coba kata kunci lain atau tambah pelanggan baru.</p>
                                    </div>
                                </td>
                            </tr>

                            <tr v-for="(p, index) in filteredList" :key="p.id_pelanggan" class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center text-gray-500 font-mono text-xs">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold shadow-sm">
                                            {{ getInitials(p.nama_pelanggan) }}
                                        </div>
                                        <span class="font-medium text-gray-900">{{ p.nama_pelanggan }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono text-xs">
                                    <div v-if="p.kontak" class="flex items-center gap-2 bg-gray-100 w-fit px-2 py-1 rounded">
                                        <Phone class="w-3 h-3 text-gray-500" />
                                        {{ p.kontak }}
                                    </div>
                                    <span v-else class="text-gray-400 italic">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openModal('edit', p)" class="p-2 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700 transition-colors" title="Edit">
                                            <Edit class="w-4 h-4"/>
                                        </button>
                                        <button @click="deleteData(p.id_pelanggan)" class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700 transition-colors" title="Hapus">
                                            <Trash2 class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-[60] p-4 animate-in fade-in duration-200">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform scale-100 transition-all" @click.stop>
                <div class="flex justify-between items-center p-5 border-b border-gray-100 bg-gray-50/50">
                    <div>
                        <h3 class="font-bold text-lg text-gray-900">{{ modalMode === 'add' ? 'Tambah' : 'Edit' }} Pelanggan</h3>
                        <p class="text-xs text-gray-500">Lengkapi form di bawah ini</p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-200 p-2 rounded-lg transition-colors">
                        <X class="w-5 h-5"/>
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Pelanggan <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input v-model="formData.nama_pelanggan" type="text" placeholder="Contoh: PT Telkom Akses"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nomor Kontak</label>
                        <div class="relative">
                            <Phone class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                            <input :value="formData.kontak" @input="ruleInputAngka" type="text" placeholder="Contoh: 08123456789"
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-sm font-mono">
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5 ml-1">Hanya angka (0-9) yang diperbolehkan.</p>
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button @click="showModal = false" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 font-medium transition-colors">Batal</button>

                        <button
                            @click="saveData"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium transition-colors flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                            <Save v-else class="w-4 h-4" />
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
</template>
