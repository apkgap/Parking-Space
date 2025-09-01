(function ($) {
  document.addEventListener('DOMContentLoaded', function () {
    const flightInput = document.getElementById('sending_flight_depart');

    // ถ้าไม่มีช่องตามไอดีนี้ ก็ไม่ต้องทำอะไร
    if (!flightInput) return;

    const group = flightInput.closest('.input-group');
    if (!group) return;

    const popup = group.querySelector('.popup_data');
    if (!popup) return;

    // ให้แน่ใจว่า parent เป็น relative (เพื่อให้ dropdown วางตำแหน่งถูก)
    if (!group.classList.contains('relative')) {
      group.classList.add('relative');
    }
    // ตั้งค่า class tailwind ให้กล่อง popup (ถ้ายังไม่ได้ใส่ใน HTML)
    popup.classList.add(
      'absolute', 'left-0', 'right-0', 'top-full', 'bg-white',
      'border', 'border-gray-200', 'rounded-lg', 'mt-1',
      'max-h-72', 'overflow-auto', 'z-50', 'shadow-xl', 'hidden'
    );

    // ===== State =====
    let debounceTimer = null;
    let results       = [];
    let activeIndex   = -1;

    // ===== Utils =====
    function toDateTimeText(iso) {
      if (!iso) return '';
      const d = new Date(iso);
      const yyyy = d.getFullYear();
      const mm   = String(d.getMonth() + 1).padStart(2, '0');
      const dd   = String(d.getDate()).padStart(2, '0');
      const hh   = String(d.getHours()).padStart(2, '0');
      const mi   = String(d.getMinutes()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd} ${hh}:${mi}`;
    }
    function toHHMM(iso) {
      if (!iso) return '';
      const d = new Date(iso);
      const hh = String(d.getHours()).padStart(2, '0');
      const mi = String(d.getMinutes()).padStart(2, '0');
      return `${hh}:${mi}`;
    }
    function toLabel(f) {
      const al     = f && f.airline && f.airline.name ? f.airline.name : '-';
      const code   = f && f.flight && (f.flight.iata || f.flight.number) ? (f.flight.iata || f.flight.number) : '-';
      const depApt = f && f.departure && f.departure.airport ? f.departure.airport : '-';
      const arrApt = f && f.arrival && f.arrival.airport ? f.arrival.airport : '-';
      const depTm  = toDateTimeText(f && f.departure && f.departure.scheduled);
      const arrTm  = toDateTimeText(f && f.arrival   && f.arrival.scheduled);
      return {
        line1: `${al} (${code})`,
        line2: `${depApt} → ${arrApt}`,
        line3: `${depTm} → ${arrTm}`
      };
    }

    function render(items) {
      results = items || [];
      activeIndex = -1;

      if (!results.length) {
        popup.innerHTML = `<div class="p-3 text-gray-500">ไม่พบข้อมูล</div>`;
        popup.classList.remove('hidden');
        return;
      }

      popup.innerHTML = results.map((f, i) => {
        const l = toLabel(f);
        const code = (f && f.flight && f.flight.iata) ? f.flight.iata : '-';
        return `
          <div
            class="flex items-start gap-2 py-2 px-3 cursor-pointer hover:bg-gray-100 aria-selected:bg-gray-100"
            role="option"
            data-index="${i}">
            <div class="font-semibold min-w-[5.5rem]">${code}</div>
            <div>
              <div class="text-gray-700">${escapeHtml(l.line1)}</div>
              <div class="text-gray-700">${escapeHtml(l.line2)}</div>
              <div class="text-gray-500 text-sm">${escapeHtml(l.line3)}</div>
            </div>
          </div>
        `;
      }).join('');

      popup.classList.remove('hidden');
    }

    function clearPopup() {
      popup.classList.add('hidden');
      popup.innerHTML = '';
      activeIndex = -1;
    }

    function pick(index) {
      const f = results[index];
      if (!f) return;

      // บันทึกลง input ที่จะส่งฟอร์ม
      const code = f && f.flight && f.flight.iata ? f.flight.iata.toUpperCase() : '';
      flightInput.value = code;

      // ถ้าต้องเก็บ JSON ทั้งก้อน ไว้ใน hidden field (ถ้าคุณมี)
      const hiddenJson = document.getElementById('sending_flight_depart_data');
      if (hiddenJson) hiddenJson.value = JSON.stringify(f);

      clearPopup();
    }

    function updateActive() {
      Array.from(popup.querySelectorAll('[role="option"]')).forEach((el, idx) => {
        el.setAttribute('aria-selected', idx === activeIndex ? 'true' : 'false');
        if (idx === activeIndex) el.scrollIntoView({ block: 'nearest' });
      });
    }

    function escapeHtml(str) {
      return String(str || '').replace(/[&<>"'`=\/]/g, function (s) {
        return ({
          '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;',
          "'": '&#39;', '/': '&#x2F;', '`': '&#x60;', '=': '&#x3D;'
        })[s];
      });
    }

    // ===== Events =====
    popup.addEventListener('click', (e) => {
      const el = e.target.closest('[role="option"]');
      if (!el) return;
      pick(parseInt(el.dataset.index, 10));
    });

    // ลูกศรขึ้น/ลง + Enter + Esc
    flightInput.addEventListener('keydown', (e) => {
      if (popup.classList.contains('hidden')) return;

      const max = results.length - 1;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(max, activeIndex + 1);
        updateActive();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(0, activeIndex - 1);
        updateActive();
      } else if (e.key === 'Enter') {
        if (activeIndex >= 0 && activeIndex <= max) {
          e.preventDefault();
          pick(activeIndex);
        }
      } else if (e.key === 'Escape') {
        clearPopup();
      }
    });

    // คลิกนอกปิด popup
    document.addEventListener('click', (e) => {
      if (!popup.contains(e.target) && e.target !== flightInput) {
        clearPopup();
      }
    });

    // ค้นหาแบบ debounce
    flightInput.addEventListener('input', () => {
      // เคลียร์เมื่อผู้ใช้พิมพ์ใหม่
      const hiddenJson = document.getElementById('sending_flight_depart_data');
      if (hiddenJson) hiddenJson.value = '';

      const q = (flightInput.value || '').trim();
      if (q.length < 2) { clearPopup(); return; }

      if (debounceTimer) clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => searchFlights(q), 300);
    });

    // ทำตัวพิมพ์ใหญ่ตอน blur
    flightInput.addEventListener('blur', () => {
      flightInput.value = (flightInput.value || '').toUpperCase();
    });

    // ===== AJAX =====
    async function searchFlights(q) {
      console.log('searchFlights called with:', q);
      console.log('akpFlightSearch object:', akpFlightSearch);
      
      try {
        const fd = new FormData();
        fd.append('action', 'akp_flight_search');
        fd.append('_ajax_nonce',  akpFlightSearch.nonce);
        fd.append('flight_iata', q);
        fd.append('limit', '15');
        fd.append('offset', '0');
        
        console.log('Sending AJAX request to:', akpFlightSearch.ajax_url);
        console.log('FormData contents:', Array.from(fd.entries()));

        const res = await fetch(akpFlightSearch.ajax_url, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        
        console.log('Response status:', res.status);
        console.log('Response ok:', res.ok);

        if (!res.ok) throw new Error('Network response was not ok: ' + res.status);
        const data = await res.json();
        console.log('Response data:', data);
        if (data && data.error) {
          throw new Error(data.error || 'Unknown error from server');
        }
        render((data && data.flights) ? data.flights : []);
      } catch (err) {
        console.error('AJAX Error:', err);
        popup.innerHTML = `<div class="p-3 text-gray-500">เกิดข้อผิดพลาดระหว่างค้นหา: ${err.message}</div>`;
        popup.classList.remove('hidden');
      }
    }
  });
})(jQuery);