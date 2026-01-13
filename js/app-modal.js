(() => {
  if (window.AppModal) return;

  const createModal = () => {
    const existing = document.getElementById("applicationModal");
    if (existing) return existing;
    const wrapper = document.createElement("div");
    wrapper.innerHTML = `
      <div class="modal" id="applicationModal" aria-hidden="true">
        <div class="modal__overlay" data-close="true"></div>
        <div class="modal__dialog" role="dialog" aria-modal="true" aria-labelledby="appModalTitle">
          <div class="modal__header">
            <h3 class="modal__title" id="appModalTitle">Заявка</h3>
            <button class="modal__close" type="button" aria-label="Закрыть" data-close="true">&times;</button>
          </div>
          <div class="modal__body" id="appModalBody">Загружаем...</div>
          <div class="modal__footer" id="appModalFooter"></div>
        </div>
      </div>
    `;
    document.body.appendChild(wrapper.firstElementChild);
    return document.getElementById("applicationModal");
  };

  const modal = createModal();
  const modalBody = modal.querySelector("#appModalBody");
  const modalFooter = modal.querySelector("#appModalFooter");

  const closeModal = () => {
    modal.classList.remove("is-active");
    document.body.style.overflow = "";
  };

  modal.addEventListener("click", (e) => {
    if (e.target.dataset.close) closeModal();
  });

  const renderVolunteers = (appId, volunteers, allowAccept) => {
    if (!volunteers || !volunteers.length) {
      return '<div class="text-muted">Пока нет откликов.</div>';
    }
    return volunteers.map((v) => `
      <div class="vol-card">
        <div class="vol-card__info">
          <div class="vol-card__name">${(v.f_name || "")} ${(v.l_name || "")}</div>
          <div class="vol-card__meta">${v.email || ""}</div>
          <div class="vol-card__meta">${v.answer || ""}</div>
        </div>
        ${allowAccept ? `<button class="btn btn--primary btn--small accept-vol-btn" data-volunteer="${v.volunteer_id}" data-app="${appId}">Принять помощь</button>` : ""}
      </div>
    `).join("");
  };

  const renderApplication = (app, volunteers, allowAccept) => {
    const endName = app.end_name || `${app.end_type || "место"} #${app.end_id || ""}`;
    const status = app.status || "pending";
    modalBody.innerHTML = `
      <div class="app-detail">
        <div class="app-detail__row"><span class="app-detail__label">Тип ограничения:</span><span>${app.type || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Адрес клиента:</span><span>${app.start_address || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Учреждение:</span><span>${endName}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Дата визита:</span><span>${app.go_date || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Комментарий:</span><span>${app.comment || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Статус:</span><span class="badge">${status}</span></div>
      </div>
      <div class="modal__section">
        <h4 class="modal__subtitle">Откликнувшиеся волонтёры</h4>
        <div id="volunteerList">${renderVolunteers(app.id, volunteers, allowAccept)}</div>
      </div>
    `;

    modalFooter.innerHTML = `<button class="btn btn--outline btn--small" type="button" data-close="true">Закрыть</button>`;

    if (allowAccept) {
      modalBody.querySelectorAll(".accept-vol-btn").forEach((btn) => {
        btn.addEventListener("click", async () => {
          const volunteerId = Number(btn.dataset.volunteer);
          btn.disabled = true;
          btn.textContent = "Принимаем...";
          try {
            const res = await fetch("api/application_accept.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                application_id: app.id,
                volunteer_id: volunteerId
              })
            });
            const text = await res.text();
            if (!res.ok) {
              let msg = "Не удалось принять помощь";
              try { const j = JSON.parse(text); msg = j.error || msg; } catch { if (text) msg = text; }
              throw new Error(msg);
            }
            btn.textContent = "Принято";
            modalBody.querySelectorAll(".accept-vol-btn").forEach((b) => { if (b !== btn) b.disabled = true; });
          } catch (err) {
            alert(err.message);
            btn.disabled = false;
            btn.textContent = "Принять помощь";
          }
        });
      });
    }
  };

  const open = async (id, { allowAccept = true } = {}) => {
    modal.classList.add("is-active");
    document.body.style.overflow = "hidden";
    modalBody.textContent = "Загружаем...";
    modalFooter.innerHTML = "";
    try {
      const res = await fetch(`api/application_detail.php?id=${encodeURIComponent(id)}`);
      const text = await res.text();
      if (!res.ok) {
        let msg = "Не удалось загрузить заявку";
        try { const j = JSON.parse(text); msg = j.error || msg; } catch { if (text) msg = text; }
        throw new Error(msg);
      }
      const data = JSON.parse(text);
      renderApplication(data.application, data.volunteers || [], allowAccept);
    } catch (err) {
      modalBody.textContent = err.message;
    }
  };

  window.AppModal = { open };
})();
