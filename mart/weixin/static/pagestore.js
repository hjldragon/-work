/*
 * 页面存储
 * QQ:15586350 [rockyshi 2014-04-02]
 */
window.Store = new function () {
  const THIS = this;
  const cur_page = window.location.pathname;

  var watch_queue = {}; // 被监控的key

  function Watcher(e) {
    e = e || window.event;
    var callback = watch_queue[e.key];
    if (callback) {
      callback({
        old_value: e.oldValue,
        new_value: e.newValue,
        from_url: e.url,
      });
    }
  }
  if (window.addEventListener) {
    window.addEventListener("storage", Watcher, false);
  }
  else {
    window.attachEvent("onstorage", Watcher);
  };

  // 给本页面发通知（在本浏览器窗口内的改动，系统不会发通知）
  function Notify(key, value) {
    var old_value = window.localStorage.getItem(key);
    if (old_value === value) {
      return;
    }
    var ev = new Event("storage");
    ev.key = key;
    ev.newValue = value;
    ev.oldValue = window.localStorage.getItem(key);
    ev.url = location.href;
    window.dispatchEvent(ev);
  }

  // 当前页面中有效
  THIS.GetPageData = function (key, defval) {
    key = "page#" + cur_page + "#" + key;
    var val = window.localStorage.getItem(key);
    return (undefined === val || null === val || "" === val) ? defval : val;
  };
  THIS.SetPageData = function (key, value) {
    key = "page#" + cur_page + "#" + key;
    Notify(key, value);
    window.localStorage.setItem(key, value);
  };
  THIS.DeletePageData = function (key) {
    key = "page#" + cur_page + "#" + key;
    Notify(key, "");
    window.localStorage.removeItem(key);
  };
  THIS.PageWatch = function (key, callback) {
    key = "page#" + cur_page + "#" + key;
    watch_queue[key] = callback;
  };

  // 当前域名下所有页面中有效
  THIS.GetGlobalData = function (key, defval) {
    key = "global#" + key;
    var val = window.localStorage.getItem(key);
    return (undefined === val || null === val || "" === val) ? defval : val;
  };
  THIS.SetGlobalData = function (key, value) {
    key = "global#" + key;
    Notify(key, value);
    window.localStorage.setItem(key, value);
  };
  THIS.DeleteGlobalData = function (key) {
    key = "global#" + key;
    Notify(key, "");
    window.localStorage.removeItem(key);
  };
  THIS.GlobalWatch = function (key, callback) {
    key = "global#" + key;
    watch_queue[key] = callback;
  };

  // 监控某个key
  THIS.Watch = function (key, callback) {
    watch_queue[key] = callback;
  };
};
