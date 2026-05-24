<div id="modal_import_staf" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center transition-opacity">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Import Data Staf</h2>
            <button type="button" onclick="document.getElementById('modal_import_staf').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                &times;
            </button>
        </div>
        
        <form action="../modules/admin/import_staf.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel (.xlsx)</label>
                <input type="file" name="file_excel" accept=".xlsx" required 
                       class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <p class="text-xs text-gray-500 mt-2">
                    Gunakan format template yang telah disediakan. Maksimal ukuran 5MB. Baris pertama (header) akan diabaikan.
                </p>
            </div>
            
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="document.getElementById('modal_import_staf').classList.add('hidden')" 
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-emerald-600 text-white font-medium rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                    Upload Data
                </button>
            </div>
        </form>
    </div>
</div>