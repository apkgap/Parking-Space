(function ($) {
    // เลือกช่องเดิมในฟอร์มของคุณ: เติมคลาส js-flight-dep / js-flight-arr ให้กับ input เหล่านั้น
    const $dep = $('#sending_flight_depart');  // ช่องเที่ยวบินขาออกเดิม
    const $arr = $('#sending_flight_arrival');  // ช่องเที่ยวบินขาเข้าเดิม
  
    // hidden fields (สร้างอัตโนมัติถ้าไม่มี)
    function ensureHidden(id) {
      let $el = $('#' + id);
      if (!$el.length) {
        $el = $('<input>', { type: 'hidden', id, name: id }).appendTo('form:first');
      }
      return $el;
    }
    const $depJson = ensureHidden('dep_json');
    const $arrJson = ensureHidden('arr_json');
  
    // ที่วางผลลัพธ์ (ถ้าไม่มีจะสร้างใต้อินพุตแรก)
    let $result = $('#flight_result');
    if (!$result.length) {
      $result = $('<div id="flight_result"></div>').insertAfter($arr.last());
    }
  
    function lookup(which, value, date) {
      if (!value) return;
      $result.append(`<div>กำลังตรวจสอบ ${which === 'dep' ? 'ขาออก' : 'ขาเข้า'}: ${value} ...</div>`);
  
      $.get(FLIGHT_LOOKUP.ajaxurl, {
        action: 'flight_lookup',
        nonce: FLIGHT_LOOKUP.nonce,
        flight: value.trim(),
        ...(date ? { date } : {})
      })
      .done((resp) => {
        if (!resp.success) {
            const msg = resp.data?.message || 'ไม่พบข้อมูล / เกิดข้อผิดพลาด';
            $result.append(`<div style="color:#b91c1c;">${msg}</div>`);
            return;
        }
        const d = resp.data;
        const html = `
          <div class="flight-card">
            <strong>${which === 'dep' ? 'ขาออก' : 'ขาเข้า'}</strong> · ${d.flight_iata} · ${d.airline || '-'} · สถานะ: ${d.status || '-'}<br>
            จาก: ${d.dep_airport} (${d.dep_iata}) เวลา ${d.dep_scheduled || '-'}<br>
            ถึง: ${d.arr_airport} (${d.arr_iata}) เวลา ${d.arr_scheduled || '-'}
          </div>`;
        $result.append(html);
  
        const json = JSON.stringify(d);
        if (which === 'dep') {
            $depJson.val(json);
            // Automatically populate departure time into the time input field
            if (d.dep_scheduled) {
                const depTime = d.dep_scheduled.split(' ')[1]; // Extract time part from datetime
                $('#sending_flight_depart_time').val(depTime);
            }
        } else {
            $arrJson.val(json);
            // Automatically populate arrival time into the time input field
            if (d.arr_scheduled) {
                const arrTime = d.arr_scheduled.split(' ')[1]; // Extract time part from datetime
                $('#sending_flight_arrival_time').val(arrTime);
            }
        }
      })
      .fail((xhr) => {
        const msg = xhr?.responseJSON?.data?.message || 'ไม่พบข้อมูล / เกิดข้อผิดพลาด';
        $result.append(`<div style="color:#b91c1c;">${msg}</div>`);
      });
    }
  
    // ถ้าฟอร์มคุณมีฟิลด์ "วันที่เดินทาง" ให้ใส่คลาส .js-flight-date (format YYYY-MM-DD)
    const $date = $('.js-flight-date');
  
    // Debounce lookups to avoid excessive API calls
    let depTimeout;
    let arrTimeout;

    $dep.on('input', () => {
        clearTimeout(depTimeout);
        $result.html(''); // Clear previous results on new input
        depTimeout = setTimeout(() => {
            lookup('dep', $dep.val(), $date.val());
        }, 500); // Wait 500ms after user stops typing
    });

    $arr.on('input', () => {
        clearTimeout(arrTimeout);
        // Do not clear the result for the second input, append to it
        arrTimeout = setTimeout(() => {
            lookup('arr', $arr.val(), $date.val());
        }, 500);
    });
  })(jQuery);