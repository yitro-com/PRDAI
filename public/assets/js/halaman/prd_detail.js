/**
 * PRDAI — Skrip halaman Tinjau Dokumen PRD.
 *
 * Menginisialisasi Mermaid secara lokal untuk merender diagram flowchart/sequence,
 * serta menyusun daftar isi (Table of Contents) dinamis dari heading dokumen.
 */
'use strict';

document.addEventListener('DOMContentLoaded', async function () {
  const dokumenEl = document.querySelector('.dokumen-prd');
  const daftarIsiNav = document.getElementById('daftar-isi-list');

  // --- 1. Susun Daftar Isi Otomatis ---
  if (dokumenEl && daftarIsiNav) {
    const headings = dokumenEl.querySelectorAll('h2, h3');
    if (headings.length > 0) {
      const frag = document.createDocumentFragment();

      headings.forEach(function (h, idx) {
        if (!h.id) {
          h.id = 'bagian-' + (idx + 1);
        }

        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = '#' + h.id;
        a.textContent = h.textContent;
        a.className = 'daftar-isi__tautan';

        if (h.tagName.toLowerCase() === 'h3') {
          a.classList.add('daftar-isi__tautan--h3');
        }

        li.appendChild(a);
        frag.appendChild(li);
      });

      daftarIsiNav.appendChild(frag);
    } else {
      const panelDaftarIsi = document.querySelector('.bilah-daftar-isi');
      if (panelDaftarIsi) {
        panelDaftarIsi.style.display = 'none';
      }
    }
  }

  // --- 2. Inisialisasi Diagram Mermaid ---
  const blokMermaid = document.querySelectorAll('pre code.language-mermaid, pre code.mermaid');

  if (blokMermaid.length > 0) {
    // Muat berkas vendor Mermaid lokal bila belum ada
    if (typeof window.mermaid === 'undefined') {
      const script = document.createElement('script');
      script.src = window.location.origin + '/PRDAI/public/assets/vendor/mermaid/mermaid.min.js';
      script.onload = function () {
        prosesDiagramMermaid();
      };
      document.head.appendChild(script);
    } else {
      prosesDiagramMermaid();
    }
  }

  function sanitasiMermaidKode(kode) {
    if (!kode) return '';
    return kode
      .replace(/(\b[A-Za-z0-9_]+)\[([^"\]]+)\]/g, function (m, id, label) {
        return id + '["' + label.replace(/"/g, "'") + '"]';
      })
      .replace(/(\b[A-Za-z0-9_]+)\{([^"\}]+)\}/g, function (m, id, label) {
        return id + '{"' + label.replace(/"/g, "'") + '"}';
      });
  }

  async function prosesDiagramMermaid() {
    if (!window.mermaid) return;

    try {
      window.mermaid.initialize({
        startOnLoad: false,
        securityLevel: 'strict',
        theme: 'neutral'
      });

      for (let i = 0; i < blokMermaid.length; i++) {
        const blok = blokMermaid[i];
        const kodeMermaid = sanitasiMermaidKode(blok.textContent.trim());
        const pre = blok.closest('pre');
        const idDiagram = 'diagram-mermaid-' + (i + 1);

        try {
          const { svg } = await window.mermaid.render(idDiagram, kodeMermaid);
          const divWadah = document.createElement('div');
          divWadah.className = 'wadah-mermaid';
          divWadah.innerHTML = svg;
          pre.parentNode.replaceChild(divWadah, pre);
        } catch (galatRender) {
          // Tetap biarkan blok kode biasa bila diagram tidak valid
          console.warn('Gagal merender diagram Mermaid:', galatRender);
        }
      }
    } catch (e) {
      console.warn('Gagal menginisialisasi Mermaid:', e);
    }
  }
});
