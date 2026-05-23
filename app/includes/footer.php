</main>
</div>

<div class="pqr-modal hide" data-pqr-modal>
  <div class="pqr-card" role="dialog" aria-modal="true" aria-labelledby="pqr-title">
    <div class="pqr-head">
      <div><strong id="pqr-title">Enviar PQR</strong><span>Petición, queja, reclamo o sugerencia al administrador</span></div>
      <button class="btn tiny" type="button" data-pqr-close>Cerrar</button>
    </div>
    <form class="pqr-form" data-pqr-form>
      <div class="form-grid cols-2">
        <div><label>Tipo</label><select name="type" required><option value="PETICION">Petición</option><option value="QUEJA">Queja</option><option value="RECLAMO">Reclamo</option><option value="SUGERENCIA">Sugerencia</option></select></div>
        <div><label>Teléfono opcional</label><input class="input" name="phone" maxlength="40" placeholder="Ej: 3001234567"></div>
      </div>
      <div><label>Asunto</label><input class="input" name="subject" maxlength="120" required placeholder="Ej: Solicitud sobre cupo de parqueadero"></div>
      <div><label>Mensaje</label><textarea class="input" name="message" rows="5" maxlength="2000" required placeholder="Describe tu petición, queja, reclamo o sugerencia..."></textarea></div>
      <div class="pqr-actions"><span class="pqr-result" data-pqr-result></span><button class="btn primary" type="submit">Enviar al administrador</button></div>
    </form>
  </div>
</div>

<div class="chatbot-widget" aria-live="polite">
  <div class="chatbot-panel hide" data-chatbot-panel>
    <div class="chatbot-head">
      <div class="chatbot-title">
        <span class="chatbot-head-icon" aria-hidden="true">🤖</span>
        <div>
          <strong>Asistente GoResidentGo</strong>
          <span>Consulta cupos, tarifas, placas y reportes</span>
        </div>
      </div>
      <button class="chatbot-close" type="button" data-chatbot-close aria-label="Cerrar asistente">×</button>
    </div>
    <div class="chatbot-messages" data-chatbot-messages>
      <div class="chatbot-message bot">
        <strong>Bot</strong>
        <span>Hola. Preguntame por cupos libres, vehiculos dentro, tarifas, residentes o una placa.</span>
      </div>
      <div class="chatbot-suggestions" data-chatbot-suggestions aria-label="Preguntas sugeridas">
        <button type="button" data-suggestion="¿Cuantos cupos libres hay?">Cupos libres</button>
        <button type="button" data-suggestion="¿Cuantos vehiculos estan dentro?">Vehiculos dentro</button>
        <button type="button" data-suggestion="¿Cuales son las tarifas?">Tarifas</button>
        <button type="button" data-suggestion="Reporte de hoy">Reporte de hoy</button>
      </div>
    </div>
    <form class="chatbot-form" data-chatbot-form>
      <input class="input" type="text" data-chatbot-input placeholder="Ej: ¿Hay cupos libres?" autocomplete="off">
      <button class="btn primary" type="submit">Enviar</button>
    </form>
  </div>
</div>
<script src="assets/js/app.js"></script>
<script src="assets/js/chatbot.js"></script>
<script src="assets/js/pqr.js"></script>
</body>
</html>