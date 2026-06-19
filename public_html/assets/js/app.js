/**
 * NWT Document Submission System - Core Javascript
 * Technology: ES6, AJAX Fetch API
 */

class NwtApp {
    /**
     * Get the CSRF token from the meta tag or form input.
     */
    static getCsrfToken() {
        const tokenInput = document.querySelector('input[name="csrf_token"]');
        return tokenInput ? tokenInput.value : '';
    }

    /**
     * Safe JSON fetch wrapper with CSRF headers.
     * @param {string} url 
     * @param {object} options 
     */
    static async secureFetch(url, options = {}) {
        const headers = options.headers || {};
        headers['X-CSRF-Token'] = this.getCsrfToken();
        headers['Accept'] = 'application/json';

        const mergedOptions = {
            ...options,
            headers: headers
        };

        const response = await fetch(url, mergedOptions);
        if (!response.ok) {
            throw new Error(`HTTP Error Status: ${response.status}`);
        }
        return await response.json();
    }

    /**
     * Format byte sizes to user-friendly string.
     * @param {number} bytes 
     */
    static formatBytes(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Local pre-upload checks.
     * @param {HTMLInputElement} fileInput 
     * @returns {boolean}
     */
    static validatePdfInput(fileInput) {
        const file = fileInput.files[0];
        if (!file) return false;

        // Size check (10MB)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`ไฟล์ "${file.name}" มีขนาดใหญ่เกินไป (ขนาดสูงสุด: 10MB)`);
            fileInput.value = ''; // clear input
            return false;
        }

        // Extension check
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'pdf') {
            alert(`ไฟล์ "${file.name}" ไม่ใช่ประเภทไฟล์ PDF`);
            fileInput.value = ''; // clear input
            return false;
        }

        return true;
    }
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    // Attach event listeners to all PDF file inputs
    const pdfInputs = document.querySelectorAll('input[type="file"][accept="application/pdf"]');
    pdfInputs.forEach(input => {
        input.addEventListener('change', () => {
            NwtApp.validatePdfInput(input);
        });
    });
});
