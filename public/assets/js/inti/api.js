/**
 * PRDAI — Klien HTTP aman berbasis Fetch (api.js).
 *
 * Mengirimkan permintaan AJAX dengan header X-Requested-With, menyertakan
 * token CSRF saat metode POST, dan otomatis memperbarui token dari respons JSON.
 */
'use strict';

window.PRDAI_API = (function () {
  async function kirim(url, data, metode) {
    metode = (metode || 'POST').toUpperCase();
    const headers = {
      'X-Requested-With': 'XMLHttpRequest'
    };

    let bodyData;

    if (metode !== 'GET' && metode !== 'HEAD') {
      if (data instanceof FormData) {
        bodyData = data;
        if (metode === 'POST' && window.PRDAI_CSRF) {
          if (!bodyData.has(window.PRDAI_CSRF.ambilNama())) {
            bodyData.append(window.PRDAI_CSRF.ambilNama(), window.PRDAI_CSRF.ambilNilai());
          }
        }
      } else if (typeof data === 'object' && data !== null) {
        const formData = new FormData();
        for (const [kunci, nilai] of Object.entries(data)) {
          if (Array.isArray(nilai)) {
            nilai.forEach(function (item) {
              formData.append(kunci + '[]', item);
            });
          } else {
            formData.append(kunci, nilai !== null ? nilai : '');
          }
        }
        if (metode === 'POST' && window.PRDAI_CSRF) {
          formData.append(window.PRDAI_CSRF.ambilNama(), window.PRDAI_CSRF.ambilNilai());
        }
        bodyData = formData;
      }
    }

    try {
      const opsiFetch = {
        method: metode,
        headers: headers,
        credentials: 'same-origin'
      };

      if (bodyData && metode !== 'GET' && metode !== 'HEAD') {
        opsiFetch.body = bodyData;
      }

      const respons = await fetch(url, opsiFetch);

      const teks = await respons.text();
      let hasil;
      try {
        hasil = JSON.parse(teks);
      } catch (e) {
        throw new Error('Respons server tidak valid.');
      }

      if (hasil && hasil.csrf && window.PRDAI_CSRF) {
        window.PRDAI_CSRF.perbarui(hasil.csrf);
      }

      return hasil;
    } catch (galat) {
      throw galat;
    }
  }

  return {
    kirim: kirim
  };
})();
