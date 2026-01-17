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
  const notifyApplicationsUpdated = () => {
    window.dispatchEvent(new CustomEvent("applications:updated"));
  };

  const closeModal = () => {
    modal.classList.remove("is-active");
    document.body.style.overflow = "";
  };

  modal.addEventListener("click", (e) => {
    if (e.target.dataset.close) closeModal();
  });

  const renderVolunteers = (appId, volunteers, allowAccept, acceptedVolunteerId) => {
    if (!volunteers || !volunteers.length) {
      return '<div class="text-muted">Пока нет откликов.</div>';
    }
    return volunteers.map((v) => `
        <div class="vol-card ${Number(v.volunteer_id) === Number(acceptedVolunteerId) ? "vol-card--accepted" : ""}">
          <div class="vol-card__info">
          <div class="vol-card__name">${(v.f_name || "")} ${(v.l_name || "")}</div>
          <div class="vol-card__meta">Выполненные отклики: ${v.closed_count ?? 0}</div>
          <div class="vol-card__meta">${v.email || ""}</div>
          <div class="vol-card__meta">${v.answer || ""}</div>
        </div>
        ${allowAccept ? `<button class="btn btn--primary btn--small accept-vol-btn" data-volunteer="${v.volunteer_id}" data-app="${appId}" data-state="${Number(v.volunteer_id) === Number(acceptedVolunteerId) ? "accepted" : "available"}">${Number(v.volunteer_id) === Number(acceptedVolunteerId) ? "Отказаться" : "Принять помощь"}</button>` : ""}
      </div>
    `).join("");
  };

  const renderStatusBadge = (status) => {
    if (status === 'closed') {
      return '<span class="badge badge--closed">Закрыта</span>';
    }
    if (status === 'open'){
      return '<span class="badge badge--opened">Открыта</span>'
    }
    if (status === 'confirmed'){
      return '<span class="badge badge--opened">Подтверждена</span>'
    }
  };

  const renderApplication = (app, volunteers, allowAccept, allowClose) => {
    const endName = app.end_name || `${app.end_type || "место"} #${app.end_id || ""}`;
    const status = app.status || "pending";
    const isClosed = status === "closed";
    const acceptedVolunteerId = app.accepted_volunteer_id ? Number(app.accepted_volunteer_id) : 0;
    const allowAcceptNow = allowAccept && !isClosed;
    modalBody.innerHTML = `
      <div class="app-detail">
        <div class="app-detail__row"><span class="app-detail__label">Тип ограничения:</span><span>${app.type || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Адрес клиента:</span><span>${app.start_address || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Учреждение:</span><span>${endName}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Дата визита:</span><span>${app.go_date || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Комментарий:</span><span>${app.comment || "—"}</span></div>
        <div class="app-detail__row"><span class="app-detail__label">Статус:</span>${renderStatusBadge(status)}</div>
      </div>
      <div class="modal__section">
        <h4 class="modal__subtitle">Откликнувшиеся волонтёры</h4>
        <div id="volunteerList" class="volunteer-list">${renderVolunteers(app.id, volunteers, allowAcceptNow, acceptedVolunteerId)}</div>
      </div>
    `;

    modalFooter.innerHTML = `
      ${allowClose ? `<button class="btn btn--outline btn--small" type="button" id="closeAppBtn" ${isClosed ? "disabled" : ""}>${isClosed ? "Заявка закрыта" : "Закрыть заявку"}</button>` : ''}
      <button class="btn btn--outline btn--small" type="button" data-close="true">Закрыть</button>
    `;

    if (allowAcceptNow) {
      const volunteerList = modalBody.querySelector("#volunteerList");
      const updateStatus = (nextStatus) => {
        const statusCell = modalBody.querySelector(".app-detail__row:last-child span:nth-child(2)");
        if (statusCell) {
          statusCell.innerHTML = renderStatusBadge(nextStatus);
        }
      };
      modalBody.querySelectorAll(".accept-vol-btn").forEach((btn) => {
        btn.addEventListener("click", async () => {
          const volunteerId = Number(btn.dataset.volunteer);
          const isAccepted = btn.dataset.state === "accepted";
          btn.disabled = true;
          btn.textContent = isAccepted ? "Отказываемся..." : "Принимаем...";
          try {
            const res = await fetch(isAccepted ? "api/application_unaccept.php" : "api/application_accept.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(isAccepted ? {
                application_id: app.id,
                volunteer_id: volunteerId
              } : {
                application_id: app.id,
                volunteer_id: volunteerId
              })
            });
            const text = await res.text();
            if (!res.ok) {
              let msg = isAccepted ? "Не удалось отказаться" : "Не удалось принять помощь";
              try { const j = JSON.parse(text); msg = j.error || msg; } catch { if (text) msg = text; }
              throw new Error(msg);
            }
            if (isAccepted) {
              btn.dataset.state = "available";
              btn.textContent = "Принять помощь";
              modalBody.querySelectorAll(".accept-vol-btn").forEach((b) => { b.disabled = false; });
              const card = btn.closest(".vol-card");
              if (card) {
                card.classList.remove("vol-card--accepted");
              }
              updateStatus("open");
            } else {
              btn.dataset.state = "accepted";
              btn.textContent = "Отказаться";
              modalBody.querySelectorAll(".accept-vol-btn").forEach((b) => { if (b !== btn) b.disabled = true; });
              if (volunteerList) {
                volunteerList.querySelectorAll(".vol-card--accepted").forEach((card) => {
                  card.classList.remove("vol-card--accepted");
                });
              }
              const card = btn.closest(".vol-card");
              if (card) {
                card.classList.add("vol-card--accepted");
              }
              updateStatus("confirmed");
            }
            btn.disabled = false;
            notifyApplicationsUpdated();
          } catch (err) {
            alert(err.message);
            btn.disabled = false;
            btn.textContent = isAccepted ? "Отказаться" : "Принять помощь";
          }
        });
      });
      if (acceptedVolunteerId) {
        modalBody.querySelectorAll(".accept-vol-btn").forEach((btn) => {
          if (Number(btn.dataset.volunteer) !== acceptedVolunteerId) {
            btn.disabled = true;
          }
        });
      }
    }

    if (allowClose && !isClosed) {
      const closeBtn = document.getElementById("closeAppBtn");
      if (closeBtn) {
        closeBtn.addEventListener("click", async () => {
          closeBtn.disabled = true;
          closeBtn.textContent = "Закрываем...";
          try {
            const res = await fetch("api/application_close.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ application_id: app.id })
            });
            const text = await res.text();
            if (!res.ok) {
              let msg = "Не удалось закрыть заявку";
              try { const j = JSON.parse(text); msg = j.error || msg; } catch { if (text) msg = text; }
              throw new Error(msg);
            }
            modalBody.querySelector(".app-detail__row:last-child span:nth-child(2)").innerHTML = renderStatusBadge("closed");
            closeBtn.textContent = "Заявка закрыта";
            closeBtn.disabled = true;
            modalBody.querySelectorAll(".accept-vol-btn").forEach((b) => { b.disabled = true; });
            notifyApplicationsUpdated();
          } catch (err) {
            alert(err.message);
            closeBtn.disabled = false;
            closeBtn.textContent = "Закрыть заявку";
          }
        });
      }
    }
  };

  const open = async (id, { allowAccept = true, allowClose = false } = {}) => {
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
      renderApplication(data.application, data.volunteers || [], allowAccept, allowClose);
    } catch (err) {
      modalBody.textContent = err.message;
    }
  };

  window.AppModal = { open };
})();
