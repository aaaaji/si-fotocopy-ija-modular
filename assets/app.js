document.addEventListener("DOMContentLoaded", () => {
  const rupiah = (n) => "Rp " + Math.round(n).toLocaleString("id-ID");
  const graph = document.getElementById("chart-data");
  const defaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: "#edf0f7" } },
      x: { grid: { display: false } },
    },
  };
  const make = (id, type, labels, values, color) => {
    const canvas = document.getElementById(id);
    if (canvas && window.Chart)
      new Chart(canvas, {
        type,
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: color,
              borderColor: "#4f46e5",
              borderWidth: type === "line" ? 3 : 0,
              tension: 0.35,
              fill: type === "line",
              pointRadius: type === "line" ? 3 : 0,
              borderRadius: type === "bar" ? 6 : 0,
            },
          ],
        },
        options: defaults,
      });
  };
  if (graph) {
    const d = JSON.parse(graph.textContent);
    make(
      "income-chart",
      "line",
      d.daily.map((v) => v.tanggal.slice(5)),
      d.daily.map((v) => Number(v.pendapatan)),
      "rgba(79,70,229,.13)",
    );
    make(
      "service-chart",
      "bar",
      d.popular.map((v) => v.nama_layanan),
      d.popular.map((v) => Number(v.unit)),
      "#8079e9",
    );
  }
  const rep = document.getElementById("report-data");
  if (rep) {
    const d = JSON.parse(rep.textContent);
    make(
      "report-chart",
      "bar",
      d.map((v) => v.tanggal),
      d.map((v) => Number(v.pendapatan)),
      "#8079e9",
    );
  }
  const rows = document.getElementById("service-rows");
  if (rows) {
    const calculate = () => {
      let total = 0;
      rows.querySelectorAll(".service-row").forEach((r) => {
        const s = r.querySelector("select"),
          q = r.querySelector("input");
        total +=
          Number(s.selectedOptions[0]?.dataset.price || 0) *
          Number(q.value || 0);
      });
      document.getElementById("order-total").textContent = rupiah(total);
    };
    rows.addEventListener("input", calculate);
    rows.addEventListener("change", calculate);
    rows.addEventListener("click", (e) => {
      if (e.target.classList.contains("remove-row")) {
        if (rows.children.length > 1) e.target.closest(".service-row").remove();
        calculate();
      }
    });
    document.getElementById("add-service").addEventListener("click", () => {
      const clone = rows.firstElementChild.cloneNode(true);
      clone.querySelector("select").value = "";
      clone.querySelector("input").value = "1";
      rows.appendChild(clone);
      calculate();
    });
    calculate();
  }
  const svc = document.getElementById("service-form");
  if (svc) {
    document.querySelectorAll(".edit-service").forEach((btn) =>
      btn.addEventListener("click", () => {
        svc.querySelector("[name=id]").value = btn.dataset.id;
        svc.querySelector("[name=nama_layanan]").value = btn.dataset.name;
        svc.querySelector("[name=tarif]").value = btn.dataset.price;
        document.getElementById("service-form-title").textContent =
          "Edit Layanan";
        svc.scrollIntoView({ behavior: "smooth" });
      }),
    );
    document.getElementById("reset-service").addEventListener("click", () => {
      svc.querySelector("[name=id]").value = "";
      document.getElementById("service-form-title").textContent =
        "Tambah Layanan";
    });
  }
});
