// TO FIX FUNCTIONS :)
/*
// Закрываем все окошки
//document.onclick  = closeAll();
// Закрыть все окошки - пока не понятно как
function closeAll() {
	var elem = document.getElementById("loginMenu");
	if (elem) {
		if (elem.hasAttribute("style")) elem.removeAttribute("style"); // если открыто - закрываем
	}
}
*/
// ==========================================================================================
// PROGRAMM FUNCTIONS
// ==========================================================================================
// создаем функцию Element.remove() для IE
if (!('remove' in Element.prototype)) {
    Element.prototype.remove = function() {if (this.parentNode) this.parentNode.removeChild(this);};
}
// Вставить elem перед блоком refElem
function insertBefr(elem, refElem) {
	return refElem.parentNode.insertBefore(elem, refElem);
}
// Вставить elem после блока refElem
function insertAfter(elem, refElem) {
	return refElem.parentNode.insertBefore(elem, refElem.nextSibling);
}
// TIME Functions
// Unixtime to format
function showTime(unixTime) { // Добавить параметр для вывода - типа месяц буквами
	var uTime = new Date(unixTime*1000);
	var yy = ("" + uTime.getFullYear()).substr(-2);
	var mo = ("0" + (uTime.getMonth()+1)).substr(-2);
	var dd = ("0" + uTime.getDate()).substr(-2);
	var hh = ("0" + uTime.getHours()).substr(-2);
	var mm = ("0" + uTime.getMinutes()).substr(-2);
	var ss = ("0" + uTime.getSeconds()).substr(-2);
	// вывод для buildComments
	return '<b>' + hh + '</b>:<b>' + mm + /*':' + ss +*/ '</b>&nbsp;<b>' + dd + '</b>.<b>' + mo + '</b>.<b>' + yy + '</b>'; // ответ для даты\время комента
}
// ==========================================================================================
// ERROR BLOCK
// ==========================================================================================
// ERROR BLOCK (id перед кем,текст,право/лево)
function showErr(id, msgText, fadeOn, waitSecs, fadeSecs) {
	// Гребаный IE не понимает DefaultValue в параметрах функции
	msgText = (typeof msgText!=='string') ? Boolean(false) : msgText;
	fadeOn = (typeof fadeOn==='undefined') ? Boolean(false) : fadeOn;
	waitSecs = (typeof waitSecs!=='number') ? 2 : waitSecs;
	fadeSecs = (typeof fadeSecs!=='number') ? 2 : fadeSecs;
	var elem = id.parentNode.getElementsByClassName("err")[0];
	if (elem) { // Если есть блок с ошибкой, то меняем текст
		if(msgText===false) elem.remove(); // без === не пашет :(
		else elem.innerHTML = msgText;
	} // Если ошибки нет, то создаем
	else if (msgText!==false) { // без !== не пашет :(
		errDiv = document.createElement("div");
		errDiv.setAttribute("class", "err");
		errDiv.innerHTML = msgText;
		insertBefr(errDiv,id);
	}
	elem = id.parentNode.getElementsByClassName("err")[0]; // подумать, как повторную проверку не делать - возможно просто закоментить :)
	if (fadeOn && elem) { // fadeOn!==false
		elem.style.transition = fadeSecs + 's opacity';
		waitSecs *= 1000;
		fadeSecs *= 1000;
		elem.style.opacity = '1';
		setTimeout(function () {elem.style.opacity = '0'}, waitSecs);
		setTimeout(function () {elem.remove()}, waitSecs+fadeSecs);
	}
}
// ==========================================================================================
// CHECK FUNCTIONS
// ==========================================================================================
// CHECK: PASSWORD FORMAT - сделать очки за (длинну, большие, цифры и т.п.) и проверять качество
function checkPassword(id) { // ID Откуда пароль
	var minLen = 5; // Минимальная длинна пароля
	if(id.value.length ==0) {
		showErr(id);
		id.removeAttribute("class");
	}
	else if(id.value.length < minLen) {
		showErr(id,'пароль должен быть больше ' + minLen + ' знаков');
		id.setAttribute("class", "input_err");
	}
	else {
		showErr(id);
		id.setAttribute("class", "input_ok");
	}
}
// CHECK: MAIL FORMAT
function checkMail(id) {
	var pattErr = /[^\w@\.]/i;
	var patt = /^\w+@\w+\.\w+$/i;
	if(pattErr.exec(id.value)!==null) {
		showErr(id,"Неверный формат почты");
		id.setAttribute("class", "input_err");
		return false;
	}
	else if(patt.exec(id.value)!==null) {
		showErr(id);
		id.removeAttribute("class");
		return true;
	}
	else {
		showErr(id);
		id.removeAttribute("class");
		return false;
	}
}
// CHECK: ALL INPUT FILDS WITH Class=input_ok
function chkAll(count) {
	var countElems = document.getElementsByClassName("input_ok").length;
	if (countElems==count) return true;
	else return false;
}
// ==========================================================================================
// POST REQUEST - MAIN FUNCTION
// ==========================================================================================
// AjaxPost(id для передачи функции ответа[как правило ссылка для вывода], параметры запроса, имя функции при ответе)
function AjaxPost(id,param,mthd) {
	var http3 = ReqObject();
	// Успешный ответ от сервака
	http3.onreadystatechange = function() {
		if (http3.readyState == 4 && http3.status == 200) {
			try {
				var jsObj = JSON.parse(http3.responseText);
			}
			catch(err) { // в случае если response не JSON - значит ошибка
				document.getElementById("crit").innerHTML = http3.responseText;
			}
			finally {
				mthd(id,jsObj); // Обрабатываем ответ(JSON) выбранной функцией(mthd) убрать в try - тут для отладки
			}
		}
	};
	// Создаем параметры, обозначаем что API
	var PostStr = "API=YES";
	for(var index in param) {
		PostStr += "&" + encodeURIComponent(index) + "=" + encodeURIComponent(param[index]);
	}
	// Запрос к серваку
	var urll = window.location.pathname; // Передаем текущий url
	http3.open("POST",urll, true);
	http3.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http3.send(PostStr);
}
// CREATE REQUEST OBJECT
function ReqObject() {
	var xmlhttp;
	if(window.XMLHttpRequest) { // code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else { // code for IE6, IE5
		xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	return xmlhttp;
}