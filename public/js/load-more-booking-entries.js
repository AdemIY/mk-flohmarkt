let offset = 20;
const ul = document.getElementById('bookingList');
const liLength = ul.children.length;
const countElement = document.getElementById('countBookings');
countElement.innerText = `${liLength}`;


document.getElementById('loadMoreBtn').addEventListener('click', function () {
    fetch(`/admin/bookings/load-more-bookings.php?offset=` + offset)
        .then(res => res.json())
        .then(data => {
            if (data.length === 0) {
                this.remove(); // keine weiteren Daten
                return;
            }


            data.forEach(b => {
                const li = document.createElement('li');
                li.className = 'p-6 even:bg-gray-50';
                li.innerHTML = `
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div class="space-y-1">
                            <h3 class="text-xl font-bold text-primary">Buchung #${b.booking_id}</h3>
                                <h4 class="text-xl font-bold text-primary">${b.first_name} ${b.last_name}</h4>
                                <p class="text-sm text-gray-500">${b.email}</p>
                                <div class="text-sm text-gray-700 mt-4 space-y-1">
                                <p><strong>💸 Bezahlt:</strong> ${b.is_paid ? 'Ja' : 'Nein'}</p>
                                    <p><strong>📅 Event:</strong> ${b.event_title}</p>
                                    <p><strong>📍 Ort:</strong> ${b.location}</p>
                                    <p><strong>🗓️ Datum:</strong> ${new Date(b.event_date).toLocaleDateString('de-DE')}</p>
                                    <p><strong>🧱 Standplatz:</strong> ${b.stand_type} – ${parseFloat(b.stand_price).toFixed(2).replace('.', ',')} €</p>
                                    ${b.message ? `<p><strong>💬 Nachricht:</strong> ${b.message}</p>` : 'Keine Nachricht hinterlassen'}
                                    <p> <strong>💸 Gesamtsumme:</strong> ${Number(b.total_amount).toLocaleString('de-DE', {
                    style: 'currency',
                    currency: 'EUR'
                })
                }
                                    </p>
                                    <p class="text-xs text-gray-500 mt-2">🕒 Gebucht am ${new Date(b.created_at).toLocaleString('de-DE')}</p>
                                </div>
                            </div>
                            <div class="bg-gray-100 p-4 rounded-xl space-y-2">
                                <p class="text-sm"><strong>✅ Teilnahmebedingungen:</strong> ${b.confirmed_booking ? 'Ja' : 'Nein'}</p>
                                <!-- Optional: Zusatzoptionen können mit separatem Fetch geladen werden -->
                                     ${b.options.length > 0 ? `
                              <div>
                                <p class="text-sm font-medium text-gray-700 mb-1">➕ Zusatzoptionen:</p>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                  ${b.options.map(opt => {
                    const price = parseFloat(opt.price);
                    return `<li>${opt.label} ${price > 0 ? '(+' + price.toFixed(2).replace('.', ',') + ' €)' : '(kostenlos)'}</li>`;
                }).join('')}
                                </ul>
                              </div>` : `<p class="text-sm text-gray-500">❌ Keine Zusatzbuchungen</p>`}
                            </div>
                        </div>
                        <div class="mt-4 flex space-x-2">
                                    <!-- Bearbeiten -->
                                    <a id="edit-booking-btn" href="edit.php?id=${b.booking_id}"
                                       class="px-4 py-2 bg-yellow-200 rounded hover:bg-yellow-300 text-sm">
                                        Bearbeiten
                                    </a>
                                    <!-- Löschen -->
                                    <form action="delete.php" method="post"
                                          onsubmit="return confirm('Buchung wirklich löschen?');">
                                        <input type="hidden" name="id" value="<?= $b['booking_id'] ?>">
                                        <button type="submit"
                                                class="px-4 py-2 bg-red-200 rounded hover:bg-red-300 text-sm">
                                            Löschen
                                        </button>
                                    </form>
                                </div>
                    `;
                ul.appendChild(li);
            });
            offset += data.length;
            const liLength = ul.children.length;
            countElement.innerText = `${liLength}`;
        });
});