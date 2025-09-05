<!-- Botón flotante del chat -->
<div id="abrirChat" class="icon" style="
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color:rgba(51, 61, 197, 1);
  color: white;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  cursor: pointer;
  z-index: 9999;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
">
  <i class="bi bi-chat-text"></i>
  <div class="label" style="font-size: 10px; margin-top: 4px;">Chat</div>
</div>

<!-- Ventana del chatbot -->
<div id="chatVentana" style="
  position: fixed;
  bottom: 90px;
  right: 20px;
  width: 350px;
  height: 450px;
  background: white;
  border: 1px solid #ccc;
  border-radius: 10px;
  display: none;
  flex-direction: column;
  z-index: 9999;
  box-shadow: 0 0 10px rgba(0,0,0,0.3);">
  
  <div style="padding: 10px; background:rgba(35, 63, 156, 1); color: white; border-radius: 10px 10px 0 0;">
    Asistente de RRHH
    <span style="float: right; cursor: pointer;" onclick="cerrarChat()">✖</span>
  </div>

  <div id="chat" style="flex: 1; padding: 10px; overflow-y: auto;"></div>

  <form id="formulario" style="display: flex; padding: 10px; border-top: 1px solid #ccc;">
    <input type="text" id="mensaje" placeholder="Escribe tu mensaje..." style="flex: 1; padding: 5px;">
    <button type="submit">➤</button>
  </form>
</div>
