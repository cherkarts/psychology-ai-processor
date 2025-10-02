/*! jQuery v3.5.1 | (c) JS Foundation and other contributors | jquery.org/license */
!(function (e, t) {
  'use strict'
  'object' == typeof module && 'object' == typeof module.exports
    ? (module.exports = e.document
        ? t(e, !0)
        : function (e) {
            if (!e.document)
              throw new Error('jQuery requires a window with a document')
            return t(e)
          })
    : t(e)
})('undefined' != typeof window ? window : this, function (C, e) {
  'use strict'
  var t = [],
    r = Object.getPrototypeOf,
    s = t.slice,
    g = t.flat
      ? function (e) {
          return t.flat.call(e)
        }
      : function (e) {
          return t.concat.apply([], e)
        },
    u = t.push,
    i = t.indexOf,
    n = {},
    o = Object.prototype.toString,
    v = n.hasOwnProperty,
    a = v.toString,
    l = a.call(Object),
    y = {},
    m = function (e) {
      return 'function' == typeof e && 'number' != typeof e.nodeType
    },
    x = function (e) {
      return null != e && e === e.window
    },
    E = C.document,
    c = { type: !0, src: !0, nonce: !0, noModule: !0 }
  function b(e, t, n) {
    var r,
      i,
      o = (n = n || E).createElement('script')
    if (((o.text = e), t))
      for (r in c)
        (i = t[r] || (t.getAttribute && t.getAttribute(r))) &&
          o.setAttribute(r, i)
    n.head.appendChild(o).parentNode.removeChild(o)
  }
  function w(e) {
    return null == e
      ? e + ''
      : 'object' == typeof e || 'function' == typeof e
      ? n[typeof o === 'function' ? o.call(e) : 'object'] || 'object'
      : typeof e
  }
  var f = '3.5.1',
    S = function (e, t) {
      return new S.fn.init(e, t)
    }
  window.jQuery = window.$ = S
  function p(e) {
    var t = !!e && 'length' in e && e.length,
      n = w(e)
    return (
      !m(e) &&
      !x(e) &&
      ('array' === n ||
        0 === t ||
        ('number' == typeof t && 0 < t && t - 1 in e))
    )
  }
  ;(S.fn = S.prototype =
    {
      jquery: f,
      constructor: S,
      length: 0,
      toArray: function () {
        return s.call(this)
      },
      get: function (e) {
        return null == e
          ? s.call(this)
          : e < 0
          ? this[e + this.length]
          : this[e]
      },
      pushStack: function (e) {
        var t = S.merge(this.constructor(), e)
        return (t.prevObject = this), t
      },
      each: function (e) {
        return S.each(this, e)
      },
      map: function (n) {
        return this.pushStack(
          S.map(this, function (e, t) {
            return n.call(e, t, e)
          })
        )
      },
      slice: function () {
        return this.pushStack(s.apply(this, arguments))
      },
      first: function () {
        return this.eq(0)
      },
      last: function () {
        return this.eq(-1)
      },
      even: function () {
        return this.pushStack(
          S.grep(this, function (e, t) {
            return (t + 1) % 2
          })
        )
      },
      odd: function () {
        return this.pushStack(
          S.grep(this, function (e, t) {
            return t % 2
          })
        )
      },
      eq: function (e) {
        var t = this.length,
          n = +e + (e < 0 ? t : 0)
        return this.pushStack(0 <= n && n < t ? [this[n]] : [])
      },
      end: function () {
        return this.prevObject || this.constructor()
      },
      push: u,
      sort: t.sort,
      splice: t.splice,
    }),
    (S.extend = S.fn.extend =
      function () {
        var e,
          t,
          n,
          r,
          i,
          o,
          a = arguments[0] || {},
          s = 1,
          u = arguments.length,
          l = !1
        for (
          'boolean' == typeof a && ((l = a), (a = arguments[s] || {}), s++),
            'object' == typeof a || m(a) || (a = {}),
            s === u && ((a = this), s--);
          s < u;
          s++
        )
          if (null != (e = arguments[s]))
            for (t in e)
              (r = e[t]),
                '__proto__' !== t &&
                  a !== r &&
                  (l && r && (S.isPlainObject(r) || (i = Array.isArray(r)))
                    ? ((n = a[t]),
                      (o =
                        i && !Array.isArray(n)
                          ? []
                          : i || S.isPlainObject(n)
                          ? n
                          : {}),
                      (i = !1),
                      (a[t] = S.extend(l, o, r)))
                    : void 0 !== r && (a[t] = r))
        return a
      }),
    S.extend({
      expando: 'jQuery' + (f + Math.random()).replace(/\D/g, ''),
      isReady: !0,
      error: function (e) {
        throw new Error(e)
      },
      noop: function () {},
      isPlainObject: function (e) {
        var t, n
        return (
          !(!e || '[object Object]' !== o.call(e)) &&
          (!(t = r(e)) ||
            ('function' ==
              typeof (n = v.call(t, 'constructor') && t.constructor) &&
              a.call(n) === l))
        )
      },
      isEmptyObject: function (e) {
        var t
        for (t in e) return !1
        return !0
      },
      globalEval: function (e, t, n) {
        b(e, { nonce: t && t.nonce }, n)
      },
      each: function (e, t) {
        var n,
          r = 0
        if (p(e)) {
          for (n = e.length; r < n; r++) if (!1 === t.call(e[r], r, e[r])) break
        } else for (r in e) if (!1 === t.call(e[r], r, e[r])) break
        return e
      },
      makeArray: function (e, t) {
        var n = t || []
        return (
          null != e &&
            (p(Object(e))
              ? S.merge(n, 'string' == typeof e ? [e] : e)
              : u.call(n, e)),
          n
        )
      },
      inArray: function (e, t, n) {
        return null == t
          ? -1
          : t && typeof t.indexOf === 'function'
          ? t.indexOf(e, n)
          : -1
      },
      merge: function (e, t) {
        for (var n = +t.length, r = 0, i = e.length; r < n; r++) e[i++] = t[r]
        return (e.length = i), e
      },
      grep: function (e, t, n) {
        for (var r = [], i = 0, o = e.length, a = !n; i < o; i++)
          !t(e[i], i) !== a && r.push(e[i])
        return r
      },
      map: function (e, t, n) {
        var r,
          i,
          o = 0,
          a = []
        if (p(e))
          for (r = e.length; o < r; o++)
            null != (i = t(e[o], o, n)) && a.push(i)
        else for (o in e) null != (i = t(e[o], o, n)) && a.push(i)
        return g(a)
      },
      guid: 1,
      support: y,
    }),
    'function' == typeof Symbol && (S.fn[Symbol.iterator] = t[Symbol.iterator]),
    S.each(
      'Boolean Number String Function Array Date RegExp Object Error Symbol'.split(
        ' '
      ),
      function (e, t) {
        n['[object ' + t + ']'] = t.toLowerCase()
      }
    )
  var d = (function (n) {
    var e,
      d,
      b,
      o,
      i,
      h,
      f,
      g,
      w,
      u,
      l,
      T,
      C,
      a,
      E,
      v,
      s,
      c,
      y,
      S = 'sizzle' + 1 * new Date(),
      p = n.document,
      k = 0,
      r = 0,
      m = ue(),
      x = ue(),
      A = ue(),
      N = ue(),
      D = function (e, t) {
        return e === t && (l = !0), 0
      },
      j = {}.hasOwnProperty,
      t = [],
      q = t.pop,
      L = t.push,
      H = t.push,
      O = t.slice,
      P = function (e, t) {
        for (var n = 0, r = e.length; n < r; n++) if (e[n] === t) return n
        return -1
      },
      R =
        'checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped',
      M = '[\\x20\\t\\r\\n\\f]',
      I =
        '(?:\\\\[\\da-fA-F]{1,6}' +
        M +
        '?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+',
      W =
        '\\[' +
        M +
        '*(' +
        I +
        ')(?:' +
        M +
        '*([*^$|!~]?=)' +
        M +
        '*(?:\'((?:\\\\.|[^\\\\\'])*)\'|"((?:\\\\.|[^\\\\"])*)"|(' +
        I +
        '))|)' +
        M +
        '*\\]',
      F =
        ':(' +
        I +
        ')(?:\\(((\'((?:\\\\.|[^\\\\\'])*)\'|"((?:\\\\.|[^\\\\"])*)")|((?:\\\\.|[^\\\\()[\\]]|' +
        W +
        ')*)|.*)\\)|)',
      B = new RegExp(M + '+', 'g'),
      $ = new RegExp('^' + M + '+|((?:^|[^\\\\])(?:\\\\.)*)' + M + '+$', 'g'),
      _ = new RegExp('^' + M + '*,' + M + '*'),
      z = new RegExp('^' + M + '*([>+~]|' + M + ')' + M + '*'),
      U = new RegExp(M + '|>'),
      X = new RegExp(F),
      V = new RegExp('^' + I + '$'),
      G = {
        ID: new RegExp('^#(' + I + ')'),
        CLASS: new RegExp('^\\.(' + I + ')'),
        TAG: new RegExp('^(' + I + '|[*])'),
        ATTR: new RegExp('^' + W),
        PSEUDO: new RegExp('^' + F),
        CHILD: new RegExp(
          '^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\(' +
            M +
            '*(even|odd|(([+-]|)(\\d*)n|)' +
            M +
            '*(?:([+-]|)' +
            M +
            '*(\\d+)|))' +
            M +
            '*\\)|)',
          'i'
        ),
        bool: new RegExp('^(?:' + R + ')$', 'i'),
        needsContext: new RegExp(
          '^' +
            M +
            '*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\(' +
            M +
            '*((?:-\\d)?\\d*)' +
            M +
            '*\\)|)(?=[^-]|$)',
          'i'
        ),
      },
      Y = /HTML$/i,
      Q = /^(?:input|select|textarea|button)$/i,
      J = /^h\d$/i,
      K = /^[^{]+\{\s*\[native \w/,
      Z = /^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,
      ee = /[+~]/,
      te = new RegExp('\\\\[\\da-fA-F]{1,6}' + M + '?|\\\\([^\\r\\n\\f])', 'g'),
      ne = function (e, t) {
        var n = '0x' + e.slice(1) - 65536
        return (
          t ||
          (n < 0
            ? String.fromCharCode(n + 65536)
            : String.fromCharCode((n >> 10) | 55296, (1023 & n) | 56320))
        )
      },
      re = /([\0-\x1f\x7f]|^-?\d)|^-$|[^\0-\x1f\x7f-\uFFFF\w-]/g,
      ie = function (e, t) {
        return t
          ? '\0' === e
            ? '\ufffd'
            : e.slice(0, -1) +
              '\\' +
              e.charCodeAt(e.length - 1).toString(16) +
              ' '
          : '\\' + e
      },
      oe = function () {
        T()
      },
      ae = be(
        function (e) {
          return !0 === e.disabled && 'fieldset' === e.nodeName.toLowerCase()
        },
        { dir: 'parentNode', next: 'legend' }
      )
    try {
      H.apply((t = O.call(p.childNodes)), p.childNodes),
        t[p.childNodes.length].nodeType
    } catch (e) {
      H = {
        apply: t.length
          ? function (e, t) {
              L.apply(e, O.call(t))
            }
          : function (e, t) {
              var n = e.length,
                r = 0
              while ((e[n++] = t[r++]));
              e.length = n - 1
            },
      }
    }
    function se(t, e, n, r) {
      var i,
        o,
        a,
        s,
        u,
        l,
        c,
        f = e && e.ownerDocument,
        p = e ? e.nodeType : 9
      if (
        ((n = n || []),
        'string' != typeof t || !t || (1 !== p && 9 !== p && 11 !== p))
      )
        return n
      if (!r && (T(e), (e = e || C), E)) {
        if (11 !== p && (u = Z.exec(t)))
          if ((i = u[1])) {
            if (9 === p) {
              if (!(a = e.getElementById(i))) return n
              if (a.id === i) return n.push(a), n
            } else if (f && (a = f.getElementById(i)) && y(e, a) && a.id === i)
              return n.push(a), n
          } else {
            if (u[2]) return H.apply(n, e.getElementsByTagName(t)), n
            if (
              (i = u[3]) &&
              d.getElementsByClassName &&
              e.getElementsByClassName
            )
              return H.apply(n, e.getElementsByClassName(i)), n
          }
        if (
          d.qsa &&
          !N[t + ' '] &&
          (!v || !v.test(t)) &&
          (1 !== p || 'object' !== e.nodeName.toLowerCase())
        ) {
          if (((c = t), (f = e), 1 === p && (U.test(t) || z.test(t)))) {
            ;((f = (ee.test(t) && ye(e.parentNode)) || e) === e && d.scope) ||
              ((s = e.getAttribute('id'))
                ? (s = s.replace(re, ie))
                : e.setAttribute('id', (s = S))),
              (o = (l = h(t)).length)
            while (o--) l[o] = (s ? '#' + s : ':scope') + ' ' + xe(l[o])
            c = l.join(',')
          }
          try {
            return H.apply(n, f.querySelectorAll(c)), n
          } catch (e) {
            N(t, !0)
          } finally {
            s === S && e.removeAttribute('id')
          }
        }
      }
      return g(t.replace($, '$1'), e, n, r)
    }
    function ue() {
      var r = []
      return function e(t, n) {
        return (
          r.push(t + ' ') > b.cacheLength && delete e[r.shift()],
          (e[t + ' '] = n)
        )
      }
    }
    function le(e) {
      return (e[S] = !0), e
    }
    function ce(e) {
      var t = C.createElement('fieldset')
      try {
        return !!e(t)
      } catch (e) {
        return !1
      } finally {
        t.parentNode && t.parentNode.removeChild(t), (t = null)
      }
    }
    function fe(e, t) {
      var n = e.split('|'),
        r = n.length
      while (r--) b.attrHandle[n[r]] = t
    }
    function pe(e, t) {
      var n = t && e,
        r =
          n &&
          1 === e.nodeType &&
          1 === t.nodeType &&
          e.sourceIndex - t.sourceIndex
      if (r) return r
      if (n) while ((n = n.nextSibling)) if (n === t) return -1
      return e ? 1 : -1
    }
    function de(t) {
      return function (e) {
        return 'input' === e.nodeName.toLowerCase() && e.type === t
      }
    }
    function he(n) {
      return function (e) {
        var t = e.nodeName.toLowerCase()
        return ('input' === t || 'button' === t) && e.type === n
      }
    }
    function ge(t) {
      return function (e) {
        return 'form' in e
          ? e.parentNode && !1 === e.disabled
            ? 'label' in e
              ? 'label' in e.parentNode
                ? e.parentNode.disabled === t
                : e.disabled === t
              : e.isDisabled === t || (e.isDisabled !== !t && ae(e) === t)
            : e.disabled === t
          : 'label' in e && e.disabled === t
      }
    }
    function ve(a) {
      return le(function (o) {
        return (
          (o = +o),
          le(function (e, t) {
            var n,
              r = a([], e.length, o),
              i = r.length
            while (i--) e[(n = r[i])] && (e[n] = !(t[n] = e[n]))
          })
        )
      })
    }
    function ye(e) {
      return e && 'undefined' != typeof e.getElementsByTagName && e
    }
    for (e in ((d = se.support = {}),
    (i = se.isXML =
      function (e) {
        var t = e.namespaceURI,
          n = (e.ownerDocument || e).documentElement
        return !Y.test(t || (n && n.nodeName) || 'HTML')
      }),
    (T = se.setDocument =
      function (e) {
        var t,
          n,
          r = e ? e.ownerDocument || e : p
        return (
          r != C &&
            9 === r.nodeType &&
            r.documentElement &&
            ((a = (C = r).documentElement),
            (E = !i(C)),
            p != C &&
              (n = C.defaultView) &&
              n.top !== n &&
              (n.addEventListener
                ? n.addEventListener('unload', oe, !1)
                : n.attachEvent && n.attachEvent('onunload', oe)),
            (d.scope = ce(function (e) {
              return (
                a.appendChild(e).appendChild(C.createElement('div')),
                'undefined' != typeof e.querySelectorAll &&
                  !e.querySelectorAll(':scope fieldset div').length
              )
            })),
            (d.attributes = ce(function (e) {
              return (e.className = 'i'), !e.getAttribute('className')
            })),
            (d.getElementsByTagName = ce(function (e) {
              return (
                e.appendChild(C.createComment('')),
                !e.getElementsByTagName('*').length
              )
            })),
            (d.getElementsByClassName = K.test(C.getElementsByClassName)),
            (d.getById = ce(function (e) {
              return (
                (a.appendChild(e).id = S),
                !C.getElementsByName || !C.getElementsByName(S).length
              )
            })),
            d.getById
              ? ((b.filter.ID = function (e) {
                  var t = e.replace(te, ne)
                  return function (e) {
                    return e.getAttribute('id') === t
                  }
                }),
                (b.find.ID = function (e, t) {
                  if ('undefined' != typeof t.getElementById && E) {
                    var n = t.getElementById(e)
                    return n ? [n] : []
                  }
                }))
              : ((b.filter.ID = function (e) {
                  var n = e.replace(te, ne)
                  return function (e) {
                    var t =
                      'undefined' != typeof e.getAttributeNode &&
                      e.getAttributeNode('id')
                    return t && t.value === n
                  }
                }),
                (b.find.ID = function (e, t) {
                  if ('undefined' != typeof t.getElementById && E) {
                    var n,
                      r,
                      i,
                      o = t.getElementById(e)
                    if (o) {
                      if ((n = o.getAttributeNode('id')) && n.value === e)
                        return [o]
                      ;(i = t.getElementsByName(e)), (r = 0)
                      while ((o = i[r++]))
                        if ((n = o.getAttributeNode('id')) && n.value === e)
                          return [o]
                    }
                    return []
                  }
                })),
            (b.find.TAG = d.getElementsByTagName
              ? function (e, t) {
                  return 'undefined' != typeof t.getElementsByTagName
                    ? t.getElementsByTagName(e)
                    : d.qsa
                    ? t.querySelectorAll(e)
                    : void 0
                }
              : function (e, t) {
                  var n,
                    r = [],
                    i = 0,
                    o = t.getElementsByTagName(e)
                  if ('*' === e) {
                    while ((n = o[i++])) 1 === n.nodeType && r.push(n)
                    return r
                  }
                  return o
                }),
            (b.find.CLASS =
              d.getElementsByClassName &&
              function (e, t) {
                if ('undefined' != typeof t.getElementsByClassName && E)
                  return t.getElementsByClassName(e)
              }),
            (s = []),
            (v = []),
            (d.qsa = K.test(C.querySelectorAll)) &&
              (ce(function (e) {
                var t
                ;(a.appendChild(e).innerHTML =
                  "<a id='" +
                  S +
                  "'></a><select id='" +
                  S +
                  "-\r\\' msallowcapture=''><option selected=''></option></select>"),
                  e.querySelectorAll("[msallowcapture^='']").length &&
                    v.push('[*^$]=' + M + '*(?:\'\'|"")'),
                  e.querySelectorAll('[selected]').length ||
                    v.push('\\[' + M + '*(?:value|' + R + ')'),
                  e.querySelectorAll('[id~=' + S + '-]').length || v.push('~='),
                  (t = C.createElement('input')).setAttribute('name', ''),
                  e.appendChild(t),
                  e.querySelectorAll("[name='']").length ||
                    v.push('\\[' + M + '*name' + M + '*=' + M + '*(?:\'\'|"")'),
                  e.querySelectorAll(':checked').length || v.push(':checked'),
                  e.querySelectorAll('a#' + S + '+*').length ||
                    v.push('.#.+[+~]'),
                  e.querySelectorAll('\\\f'),
                  v.push('[\\r\\n\\f]')
              }),
              ce(function (e) {
                e.innerHTML =
                  "<a href='' disabled='disabled'></a><select disabled='disabled'><option/></select>"
                var t = C.createElement('input')
                t.setAttribute('type', 'hidden'),
                  e.appendChild(t).setAttribute('name', 'D'),
                  e.querySelectorAll('[name=d]').length &&
                    v.push('name' + M + '*[*^$|!~]?='),
                  2 !== e.querySelectorAll(':enabled').length &&
                    v.push(':enabled', ':disabled'),
                  (a.appendChild(e).disabled = !0),
                  2 !== e.querySelectorAll(':disabled').length &&
                    v.push(':enabled', ':disabled'),
                  e.querySelectorAll('*,:x'),
                  v.push(',.*:')
              })),
            (d.matchesSelector = K.test(
              (c =
                a.matches ||
                a.webkitMatchesSelector ||
                a.mozMatchesSelector ||
                a.oMatchesSelector ||
                a.msMatchesSelector)
            )) &&
              ce(function (e) {
                ;(d.disconnectedMatch = c.call(e, '*')),
                  c.call(e, "[s!='']:x"),
                  s.push('!=', F)
              }),
            (v = v.length && new RegExp(v.join('|'))),
            (s = s.length && new RegExp(s.join('|'))),
            (t = K.test(a.compareDocumentPosition)),
            (y =
              t || K.test(a.contains)
                ? function (e, t) {
                    var n = 9 === e.nodeType ? e.documentElement : e,
                      r = t && t.parentNode
                    return (
                      e === r ||
                      !(
                        !r ||
                        1 !== r.nodeType ||
                        !(n.contains
                          ? n.contains(r)
                          : e.compareDocumentPosition &&
                            16 & e.compareDocumentPosition(r))
                      )
                    )
                  }
                : function (e, t) {
                    if (t) while ((t = t.parentNode)) if (t === e) return !0
                    return !1
                  }),
            (D = t
              ? function (e, t) {
                  if (e === t) return (l = !0), 0
                  var n =
                    !e.compareDocumentPosition - !t.compareDocumentPosition
                  return (
                    n ||
                    (1 &
                      (n =
                        (e.ownerDocument || e) == (t.ownerDocument || t)
                          ? e.compareDocumentPosition(t)
                          : 1) ||
                    (!d.sortDetached && t.compareDocumentPosition(e) === n)
                      ? e == C || (e.ownerDocument == p && y(p, e))
                        ? -1
                        : t == C || (t.ownerDocument == p && y(p, t))
                        ? 1
                        : u
                        ? P(u, e) - P(u, t)
                        : 0
                      : 4 & n
                      ? -1
                      : 1)
                  )
                }
              : function (e, t) {
                  if (e === t) return (l = !0), 0
                  var n,
                    r = 0,
                    i = e.parentNode,
                    o = t.parentNode,
                    a = [e],
                    s = [t]
                  if (!i || !o)
                    return e == C
                      ? -1
                      : t == C
                      ? 1
                      : i
                      ? -1
                      : o
                      ? 1
                      : u
                      ? P(u, e) - P(u, t)
                      : 0
                  if (i === o) return pe(e, t)
                  n = e
                  while ((n = n.parentNode)) a.unshift(n)
                  n = t
                  while ((n = n.parentNode)) s.unshift(n)
                  while (a[r] === s[r]) r++
                  return r ? pe(a[r], s[r]) : a[r] == p ? -1 : s[r] == p ? 1 : 0
                })),
          C
        )
      }),
    (se.matches = function (e, t) {
      return se(e, null, null, t)
    }),
    (se.matchesSelector = function (e, t) {
      if (
        (T(e),
        d.matchesSelector &&
          E &&
          !N[t + ' '] &&
          (!s || !s.test(t)) &&
          (!v || !v.test(t)))
      )
        try {
          var n = c.call(e, t)
          if (
            n ||
            d.disconnectedMatch ||
            (e.document && 11 !== e.document.nodeType)
          )
            return n
        } catch (e) {
          N(t, !0)
        }
      return 0 < se(t, C, null, [e]).length
    }),
    (se.contains = function (e, t) {
      return (e.ownerDocument || e) != C && T(e), y(e, t)
    }),
    (se.attr = function (e, t) {
      ;(e.ownerDocument || e) != C && T(e)
      var n = b.attrHandle[t.toLowerCase()],
        r = n && j.call(b.attrHandle, t.toLowerCase()) ? n(e, t, !E) : void 0
      return void 0 !== r
        ? r
        : d.attributes || !E
        ? e.getAttribute(t)
        : (r = e.getAttributeNode(t)) && r.specified
        ? r.value
        : null
    }),
    (se.escape = function (e) {
      return (e + '').replace(re, ie)
    }),
    (se.error = function (e) {
      throw new Error('Syntax error, unrecognized expression: ' + e)
    }),
    (se.uniqueSort = function (e) {
      var t,
        n = [],
        r = 0,
        i = 0
      if (
        ((l = !d.detectDuplicates),
        (u = !d.sortStable && e.slice(0)),
        e.sort(D),
        l)
      ) {
        while ((t = e[i++])) t === e[i] && (r = n.push(i))
        while (r--) e.splice(n[r], 1)
      }
      return (u = null), e
    }),
    (o = se.getText =
      function (e) {
        var t,
          n = '',
          r = 0,
          i = e.nodeType
        if (i) {
          if (1 === i || 9 === i || 11 === i) {
            if ('string' == typeof e.textContent) return e.textContent
            for (e = e.firstChild; e; e = e.nextSibling) n += o(e)
          } else if (3 === i || 4 === i) return e.nodeValue
        } else while ((t = e[r++])) n += o(t)
        return n
      }),
    ((b = se.selectors =
      {
        cacheLength: 50,
        createPseudo: le,
        match: G,
        attrHandle: {},
        find: {},
        relative: {
          '>': { dir: 'parentNode', first: !0 },
          ' ': { dir: 'parentNode' },
          '+': { dir: 'previousSibling', first: !0 },
          '~': { dir: 'previousSibling' },
        },
        preFilter: {
          ATTR: function (e) {
            return (
              (e[1] = e[1].replace(te, ne)),
              (e[3] = (e[3] || e[4] || e[5] || '').replace(te, ne)),
              '~=' === e[2] && (e[3] = ' ' + e[3] + ' '),
              e.slice(0, 4)
            )
          },
          CHILD: function (e) {
            return (
              (e[1] = e[1].toLowerCase()),
              'nth' === e[1].slice(0, 3)
                ? (e[3] || se.error(e[0]),
                  (e[4] = +(e[4]
                    ? e[5] + (e[6] || 1)
                    : 2 * ('even' === e[3] || 'odd' === e[3]))),
                  (e[5] = +(e[7] + e[8] || 'odd' === e[3])))
                : e[3] && se.error(e[0]),
              e
            )
          },
          PSEUDO: function (e) {
            var t,
              n = !e[6] && e[2]
            return G.CHILD.test(e[0])
              ? null
              : (e[3]
                  ? (e[2] = e[4] || e[5] || '')
                  : n &&
                    X.test(n) &&
                    (t = h(n, !0)) &&
                    (t = n.indexOf(')', n.length - t) - n.length) &&
                    ((e[0] = e[0].slice(0, t)), (e[2] = n.slice(0, t))),
                e.slice(0, 3))
          },
        },
        filter: {
          TAG: function (e) {
            var t = e.replace(te, ne).toLowerCase()
            return '*' === e
              ? function () {
                  return !0
                }
              : function (e) {
                  return e.nodeName && e.nodeName.toLowerCase() === t
                }
          },
          CLASS: function (e) {
            var t = m[e + ' ']
            return (
              t ||
              ((t = new RegExp('(^|' + M + ')' + e + '(' + M + '|$)')) &&
                m(e, function (e) {
                  return t.test(
                    ('string' == typeof e.className && e.className) ||
                      ('undefined' != typeof e.getAttribute &&
                        e.getAttribute('class')) ||
                      ''
                  )
                }))
            )
          },
          ATTR: function (n, r, i) {
            return function (e) {
              var t = se.attr(e, n)
              return null == t
                ? '!=' === r
                : !r ||
                    ((t += ''),
                    '=' === r
                      ? t === i
                      : '!=' === r
                      ? t !== i
                      : '^=' === r
                      ? i && 0 === t.indexOf(i)
                      : '*=' === r
                      ? i && -1 < t.indexOf(i)
                      : '$=' === r
                      ? i && t.slice(-i.length) === i
                      : '~=' === r
                      ? -1 < (' ' + t.replace(B, ' ') + ' ').indexOf(i)
                      : '|=' === r &&
                        (t === i || t.slice(0, i.length + 1) === i + '-'))
            }
          },
          CHILD: function (h, e, t, g, v) {
            var y = 'nth' !== h.slice(0, 3),
              m = 'last' !== h.slice(-4),
              x = 'of-type' === e
            return 1 === g && 0 === v
              ? function (e) {
                  return !!e.parentNode
                }
              : function (e, t, n) {
                  var r,
                    i,
                    o,
                    a,
                    s,
                    u,
                    l = y !== m ? 'nextSibling' : 'previousSibling',
                    c = e.parentNode,
                    f = x && e.nodeName.toLowerCase(),
                    p = !n && !x,
                    d = !1
                  if (c) {
                    if (y) {
                      while (l) {
                        a = e
                        while ((a = a[l]))
                          if (
                            x
                              ? a.nodeName.toLowerCase() === f
                              : 1 === a.nodeType
                          )
                            return !1
                        u = l = 'only' === h && !u && 'nextSibling'
                      }
                      return !0
                    }
                    if (((u = [m ? c.firstChild : c.lastChild]), m && p)) {
                      ;(d =
                        (s =
                          (r =
                            (i =
                              (o = (a = c)[S] || (a[S] = {}))[a.uniqueID] ||
                              (o[a.uniqueID] = {}))[h] || [])[0] === k &&
                          r[1]) && r[2]),
                        (a = s && c.childNodes[s])
                      while ((a = (++s && a && a[l]) || (d = s = 0) || u.pop()))
                        if (1 === a.nodeType && ++d && a === e) {
                          i[h] = [k, s, d]
                          break
                        }
                    } else if (
                      (p &&
                        (d = s =
                          (r =
                            (i =
                              (o = (a = e)[S] || (a[S] = {}))[a.uniqueID] ||
                              (o[a.uniqueID] = {}))[h] || [])[0] === k && r[1]),
                      !1 === d)
                    )
                      while ((a = (++s && a && a[l]) || (d = s = 0) || u.pop()))
                        if (
                          (x
                            ? a.nodeName.toLowerCase() === f
                            : 1 === a.nodeType) &&
                          ++d &&
                          (p &&
                            ((i =
                              (o = a[S] || (a[S] = {}))[a.uniqueID] ||
                              (o[a.uniqueID] = {}))[h] = [k, d]),
                          a === e)
                        )
                          break
                    return (d -= v) === g || (d % g == 0 && 0 <= d / g)
                  }
                }
          },
          PSEUDO: function (e, o) {
            var t,
              a =
                b.pseudos[e] ||
                b.setFilters[e.toLowerCase()] ||
                se.error('unsupported pseudo: ' + e)
            return a[S]
              ? a(o)
              : 1 < a.length
              ? ((t = [e, e, '', o]),
                b.setFilters.hasOwnProperty(e.toLowerCase())
                  ? le(function (e, t) {
                      var n,
                        r = a(e, o),
                        i = r.length
                      while (i--) e[(n = P(e, r[i]))] = !(t[n] = r[i])
                    })
                  : function (e) {
                      return a(e, 0, t)
                    })
              : a
          },
        },
        pseudos: {
          not: le(function (e) {
            var r = [],
              i = [],
              s = f(e.replace($, '$1'))
            return s[S]
              ? le(function (e, t, n, r) {
                  var i,
                    o = s(e, null, r, []),
                    a = e.length
                  while (a--) (i = o[a]) && (e[a] = !(t[a] = i))
                })
              : function (e, t, n) {
                  return (r[0] = e), s(r, null, n, i), (r[0] = null), !i.pop()
                }
          }),
          has: le(function (t) {
            return function (e) {
              return 0 < se(t, e).length
            }
          }),
          contains: le(function (t) {
            return (
              (t = t.replace(te, ne)),
              function (e) {
                return -1 < (e.textContent || o(e)).indexOf(t)
              }
            )
          }),
          lang: le(function (n) {
            return (
              V.test(n || '') || se.error('unsupported lang: ' + n),
              (n = n.replace(te, ne).toLowerCase()),
              function (e) {
                var t
                do {
                  if (
                    (t = E
                      ? e.lang
                      : e.getAttribute('xml:lang') || e.getAttribute('lang'))
                  )
                    return (
                      (t = t.toLowerCase()) === n || 0 === t.indexOf(n + '-')
                    )
                } while ((e = e.parentNode) && 1 === e.nodeType)
                return !1
              }
            )
          }),
          target: function (e) {
            var t = n.location && n.location.hash
            return t && t.slice(1) === e.id
          },
          root: function (e) {
            return e === a
          },
          focus: function (e) {
            return (
              e === C.activeElement &&
              (!C.hasFocus || C.hasFocus()) &&
              !!(e.type || e.href || ~e.tabIndex)
            )
          },
          enabled: ge(!1),
          disabled: ge(!0),
          checked: function (e) {
            var t = e.nodeName.toLowerCase()
            return (
              ('input' === t && !!e.checked) || ('option' === t && !!e.selected)
            )
          },
          selected: function (e) {
            return e.parentNode && e.parentNode.selectedIndex, !0 === e.selected
          },
          empty: function (e) {
            for (e = e.firstChild; e; e = e.nextSibling)
              if (e.nodeType < 6) return !1
            return !0
          },
          parent: function (e) {
            return !b.pseudos.empty(e)
          },
          header: function (e) {
            return J.test(e.nodeName)
          },
          input: function (e) {
            return Q.test(e.nodeName)
          },
          button: function (e) {
            var t = e.nodeName.toLowerCase()
            return ('input' === t && 'button' === e.type) || 'button' === t
          },
          text: function (e) {
            var t
            return (
              'input' === e.nodeName.toLowerCase() &&
              'text' === e.type &&
              (null == (t = e.getAttribute('type')) ||
                'text' === t.toLowerCase())
            )
          },
          first: ve(function () {
            return [0]
          }),
          last: ve(function (e, t) {
            return [t - 1]
          }),
          eq: ve(function (e, t, n) {
            return [n < 0 ? n + t : n]
          }),
          even: ve(function (e, t) {
            for (var n = 0; n < t; n += 2) e.push(n)
            return e
          }),
          odd: ve(function (e, t) {
            for (var n = 1; n < t; n += 2) e.push(n)
            return e
          }),
          lt: ve(function (e, t, n) {
            for (var r = n < 0 ? n + t : t < n ? t : n; 0 <= --r; ) e.push(r)
            return e
          }),
          gt: ve(function (e, t, n) {
            for (var r = n < 0 ? n + t : n; ++r < t; ) e.push(r)
            return e
          }),
        },
      }).pseudos.nth = b.pseudos.eq),
    { radio: !0, checkbox: !0, file: !0, password: !0, image: !0 }))
      b.pseudos[e] = de(e)
    for (e in { submit: !0, reset: !0 }) b.pseudos[e] = he(e)
    function me() {}
    function xe(e) {
      for (var t = 0, n = e.length, r = ''; t < n; t++) r += e[t].value
      return r
    }
    function be(s, e, t) {
      var u = e.dir,
        l = e.next,
        c = l || u,
        f = t && 'parentNode' === c,
        p = r++
      return e.first
        ? function (e, t, n) {
            while ((e = e[u])) if (1 === e.nodeType || f) return s(e, t, n)
            return !1
          }
        : function (e, t, n) {
            var r,
              i,
              o,
              a = [k, p]
            if (n) {
              while ((e = e[u]))
                if ((1 === e.nodeType || f) && s(e, t, n)) return !0
            } else
              while ((e = e[u]))
                if (1 === e.nodeType || f)
                  if (
                    ((i =
                      (o = e[S] || (e[S] = {}))[e.uniqueID] ||
                      (o[e.uniqueID] = {})),
                    l && l === e.nodeName.toLowerCase())
                  )
                    e = e[u] || e
                  else {
                    if ((r = i[c]) && r[0] === k && r[1] === p)
                      return (a[2] = r[2])
                    if (((i[c] = a)[2] = s(e, t, n))) return !0
                  }
            return !1
          }
    }
    function we(i) {
      return 1 < i.length
        ? function (e, t, n) {
            var r = i.length
            while (r--) if (!i[r](e, t, n)) return !1
            return !0
          }
        : i[0]
    }
    function Te(e, t, n, r, i) {
      for (var o, a = [], s = 0, u = e.length, l = null != t; s < u; s++)
        (o = e[s]) && ((n && !n(o, r, i)) || (a.push(o), l && t.push(s)))
      return a
    }
    function Ce(d, h, g, v, y, e) {
      return (
        v && !v[S] && (v = Ce(v)),
        y && !y[S] && (y = Ce(y, e)),
        le(function (e, t, n, r) {
          var i,
            o,
            a,
            s = [],
            u = [],
            l = t.length,
            c =
              e ||
              (function (e, t, n) {
                for (var r = 0, i = t.length; r < i; r++) se(e, t[r], n)
                return n
              })(h || '*', n.nodeType ? [n] : n, []),
            f = !d || (!e && h) ? c : Te(c, s, d, n, r),
            p = g ? (y || (e ? d : l || v) ? [] : t) : f
          if ((g && g(f, p, n, r), v)) {
            ;(i = Te(p, u)), v(i, [], n, r), (o = i.length)
            while (o--) (a = i[o]) && (p[u[o]] = !(f[u[o]] = a))
          }
          if (e) {
            if (y || d) {
              if (y) {
                ;(i = []), (o = p.length)
                while (o--) (a = p[o]) && i.push((f[o] = a))
                y(null, (p = []), i, r)
              }
              o = p.length
              while (o--)
                (a = p[o]) &&
                  -1 < (i = y ? P(e, a) : s[o]) &&
                  (e[i] = !(t[i] = a))
            }
          } else (p = Te(p === t ? p.splice(l, p.length) : p)), y ? y(null, t, p, r) : H.apply(t, p)
        })
      )
    }
    function Ee(e) {
      for (
        var i,
          t,
          n,
          r = e.length,
          o = b.relative[e[0].type],
          a = o || b.relative[' '],
          s = o ? 1 : 0,
          u = be(
            function (e) {
              return e === i
            },
            a,
            !0
          ),
          l = be(
            function (e) {
              return -1 < P(i, e)
            },
            a,
            !0
          ),
          c = [
            function (e, t, n) {
              var r =
                (!o && (n || t !== w)) ||
                ((i = t).nodeType ? u(e, t, n) : l(e, t, n))
              return (i = null), r
            },
          ];
        s < r;
        s++
      )
        if ((t = b.relative[e[s].type])) c = [be(we(c), t)]
        else {
          if ((t = b.filter[e[s].type].apply(null, e[s].matches))[S]) {
            for (n = ++s; n < r; n++) if (b.relative[e[n].type]) break
            return Ce(
              1 < s && we(c),
              1 < s &&
                xe(
                  e
                    .slice(0, s - 1)
                    .concat({ value: ' ' === e[s - 2].type ? '*' : '' })
                ).replace($, '$1'),
              t,
              s < n && Ee(e.slice(s, n)),
              n < r && Ee((e = e.slice(n))),
              n < r && xe(e)
            )
          }
          c.push(t)
        }
      return we(c)
    }
    return (
      (me.prototype = b.filters = b.pseudos),
      (b.setFilters = new me()),
      (h = se.tokenize =
        function (e, t) {
          var n,
            r,
            i,
            o,
            a,
            s,
            u,
            l = x[e + ' ']
          if (l) return t ? 0 : l.slice(0)
          ;(a = e), (s = []), (u = b.preFilter)
          while (a) {
            for (o in ((n && !(r = _.exec(a))) ||
              (r && (a = a.slice(r[0].length) || a), s.push((i = []))),
            (n = !1),
            (r = z.exec(a)) &&
              ((n = r.shift()),
              i.push({ value: n, type: r[0].replace($, ' ') }),
              (a = a.slice(n.length))),
            b.filter))
              !(r = G[o].exec(a)) ||
                (u[o] && !(r = u[o](r))) ||
                ((n = r.shift()),
                i.push({ value: n, type: o, matches: r }),
                (a = a.slice(n.length)))
            if (!n) break
          }
          return t ? a.length : a ? se.error(e) : x(e, s).slice(0)
        }),
      (f = se.compile =
        function (e, t) {
          var n,
            v,
            y,
            m,
            x,
            r,
            i = [],
            o = [],
            a = A[e + ' ']
          if (!a) {
            t || (t = h(e)), (n = t.length)
            while (n--) (a = Ee(t[n]))[S] ? i.push(a) : o.push(a)
            ;(a = A(
              e,
              ((v = o),
              (m = 0 < (y = i).length),
              (x = 0 < v.length),
              (r = function (e, t, n, r, i) {
                var o,
                  a,
                  s,
                  u = 0,
                  l = '0',
                  c = e && [],
                  f = [],
                  p = w,
                  d = e || (x && b.find.TAG('*', i)),
                  h = (k += null == p ? 1 : Math.random() || 0.1),
                  g = d.length
                for (
                  i && (w = t == C || t || i);
                  l !== g && null != (o = d[l]);
                  l++
                ) {
                  if (x && o) {
                    ;(a = 0), t || o.ownerDocument == C || (T(o), (n = !E))
                    while ((s = v[a++]))
                      if (s(o, t || C, n)) {
                        r.push(o)
                        break
                      }
                    i && (k = h)
                  }
                  m && ((o = !s && o) && u--, e && c.push(o))
                }
                if (((u += l), m && l !== u)) {
                  a = 0
                  while ((s = y[a++])) s(c, f, t, n)
                  if (e) {
                    if (0 < u) while (l--) c[l] || f[l] || (f[l] = q.call(r))
                    f = Te(f)
                  }
                  H.apply(r, f),
                    i &&
                      !e &&
                      0 < f.length &&
                      1 < u + y.length &&
                      se.uniqueSort(r)
                }
                return i && ((k = h), (w = p)), c
              }),
              m ? le(r) : r)
            )).selector = e
          }
          return a
        }),
      (g = se.select =
        function (e, t, n, r) {
          var i,
            o,
            a,
            s,
            u,
            l = 'function' == typeof e && e,
            c = !r && h((e = l.selector || e))
          if (((n = n || []), 1 === c.length)) {
            if (
              2 < (o = c[0] = c[0].slice(0)).length &&
              'ID' === (a = o[0]).type &&
              9 === t.nodeType &&
              E &&
              b.relative[o[1].type]
            ) {
              if (!(t = (b.find.ID(a.matches[0].replace(te, ne), t) || [])[0]))
                return n
              l && (t = t.parentNode), (e = e.slice(o.shift().value.length))
            }
            i = G.needsContext.test(e) ? 0 : o.length
            while (i--) {
              if (((a = o[i]), b.relative[(s = a.type)])) break
              if (
                (u = b.find[s]) &&
                (r = u(
                  a.matches[0].replace(te, ne),
                  (ee.test(o[0].type) && ye(t.parentNode)) || t
                ))
              ) {
                if ((o.splice(i, 1), !(e = r.length && xe(o))))
                  return H.apply(n, r), n
                break
              }
            }
          }
          return (
            (l || f(e, c))(
              r,
              t,
              !E,
              n,
              !t || (ee.test(e) && ye(t.parentNode)) || t
            ),
            n
          )
        }),
      (d.sortStable = S.split('').sort(D).join('') === S),
      (d.detectDuplicates = !!l),
      T(),
      (d.sortDetached = ce(function (e) {
        return 1 & e.compareDocumentPosition(C.createElement('fieldset'))
      })),
      ce(function (e) {
        return (
          (e.innerHTML = "<a href='#'></a>"),
          '#' === e.firstChild.getAttribute('href')
        )
      }) ||
        fe('type|href|height|width', function (e, t, n) {
          if (!n) return e.getAttribute(t, 'type' === t.toLowerCase() ? 1 : 2)
        }),
      (d.attributes &&
        ce(function (e) {
          return (
            (e.innerHTML = '<input/>'),
            e.firstChild.setAttribute('value', ''),
            '' === e.firstChild.getAttribute('value')
          )
        })) ||
        fe('value', function (e, t, n) {
          if (!n && 'input' === e.nodeName.toLowerCase()) return e.defaultValue
        }),
      ce(function (e) {
        return null == e.getAttribute('disabled')
      }) ||
        fe(R, function (e, t, n) {
          var r
          if (!n)
            return !0 === e[t]
              ? t.toLowerCase()
              : (r = e.getAttributeNode(t)) && r.specified
              ? r.value
              : null
        }),
      se
    )
  })(C)
  ;(S.find = d),
    (S.expr = d.selectors),
    (S.expr[':'] = S.expr.pseudos),
    (S.uniqueSort = S.unique = d.uniqueSort),
    (S.text = d.getText),
    (S.isXMLDoc = d.isXML),
    (S.contains = d.contains),
    (S.escapeSelector = d.escape)
  var h = function (e, t, n) {
      var r = [],
        i = void 0 !== n
      while ((e = e[t]) && 9 !== e.nodeType)
        if (1 === e.nodeType) {
          if (i && S(e).is(n)) break
          r.push(e)
        }
      return r
    },
    T = function (e, t) {
      for (var n = []; e; e = e.nextSibling)
        1 === e.nodeType && e !== t && n.push(e)
      return n
    },
    k = S.expr.match.needsContext
  function A(e, t) {
    return e.nodeName && e.nodeName.toLowerCase() === t.toLowerCase()
  }
  var N = /^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i
  function D(e, n, r) {
    return m(n)
      ? S.grep(e, function (e, t) {
          return !!n.call(e, t, e) !== r
        })
      : n.nodeType
      ? S.grep(e, function (e) {
          return (e === n) !== r
        })
      : 'string' != typeof n
      ? S.grep(e, function (e) {
          return (
            -1 <
              (i && typeof i === 'function' ? i.call(n, e) : n.indexOf(e)) !==
            r
          )
        })
      : S.filter(n, e, r)
  }
  ;(S.filter = function (e, t, n) {
    var r = t[0]
    return (
      n && (e = ':not(' + e + ')'),
      1 === t.length && 1 === r.nodeType
        ? S.find.matchesSelector(r, e)
          ? [r]
          : []
        : S.find.matches(
            e,
            S.grep(t, function (e) {
              return 1 === e.nodeType
            })
          )
    )
  }),
    S.fn.extend({
      find: function (e) {
        var t,
          n,
          r = this.length,
          i = this
        if ('string' != typeof e)
          return this.pushStack(
            S(e).filter(function () {
              for (t = 0; t < r; t++) if (S.contains(i[t], this)) return !0
            })
          )
        for (n = this.pushStack([]), t = 0; t < r; t++) S.find(e, i[t], n)
        return 1 < r ? S.uniqueSort(n) : n
      },
      filter: function (e) {
        return this.pushStack(D(this, e || [], !1))
      },
      not: function (e) {
        return this.pushStack(D(this, e || [], !0))
      },
      is: function (e) {
        return !!D(this, 'string' == typeof e && k.test(e) ? S(e) : e || [], !1)
          .length
      },
    })
  var j,
    q = /^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/
  ;((S.fn.init = function (e, t, n) {
    var r, i
    if (!e) return this
    if (((n = n || j), 'string' == typeof e)) {
      if (
        !(r =
          '<' === e[0] && '>' === e[e.length - 1] && 3 <= e.length
            ? [null, e, null]
            : q.exec(e)) ||
        (!r[1] && t)
      )
        return !t || t.jquery ? (t || n).find(e) : this.constructor(t).find(e)
      if (r[1]) {
        if (
          ((t = t instanceof S ? t[0] : t),
          S.merge(
            this,
            S.parseHTML(r[1], t && t.nodeType ? t.ownerDocument || t : E, !0)
          ),
          N.test(r[1]) && S.isPlainObject(t))
        )
          for (r in t) m(this[r]) ? this[r](t[r]) : this.attr(r, t[r])
        return this
      }
      return (
        (i = E.getElementById(r[2])) && ((this[0] = i), (this.length = 1)), this
      )
    }
    return e.nodeType
      ? ((this[0] = e), (this.length = 1), this)
      : m(e)
      ? void 0 !== n.ready
        ? n.ready(e)
        : e(S)
      : S.makeArray(e, this)
  }).prototype = S.fn),
    (j = S(E))
  var L = /^(?:parents|prev(?:Until|All))/,
    H = { children: !0, contents: !0, next: !0, prev: !0 }
  function O(e, t) {
    while ((e = e[t]) && 1 !== e.nodeType);
    return e
  }
  S.fn.extend({
    has: function (e) {
      var t = S(e, this),
        n = t.length
      return this.filter(function () {
        for (var e = 0; e < n; e++) if (S.contains(this, t[e])) return !0
      })
    },
    closest: function (e, t) {
      var n,
        r = 0,
        i = this.length,
        o = [],
        a = 'string' != typeof e && S(e)
      if (!k.test(e))
        for (; r < i; r++)
          for (n = this[r]; n && n !== t; n = n.parentNode)
            if (
              n.nodeType < 11 &&
              (a
                ? -1 < a.index(n)
                : 1 === n.nodeType && S.find.matchesSelector(n, e))
            ) {
              o.push(n)
              break
            }
      return this.pushStack(1 < o.length ? S.uniqueSort(o) : o)
    },
    index: function (e) {
      return e
        ? 'string' == typeof e
          ? i && typeof i === 'function'
            ? i.call(S(e), this[0])
            : S(e).indexOf(this[0])
          : i && typeof i === 'function'
          ? i.call(this, e.jquery ? e[0] : e)
          : this.indexOf(e.jquery ? e[0] : e)
        : this[0] && this[0].parentNode
        ? this.first().prevAll().length
        : -1
    },
    add: function (e, t) {
      return this.pushStack(S.uniqueSort(S.merge(this.get(), S(e, t))))
    },
    addBack: function (e) {
      return this.add(null == e ? this.prevObject : this.prevObject.filter(e))
    },
  }),
    S.each(
      {
        parent: function (e) {
          var t = e.parentNode
          return t && 11 !== t.nodeType ? t : null
        },
        parents: function (e) {
          return h(e, 'parentNode')
        },
        parentsUntil: function (e, t, n) {
          return h(e, 'parentNode', n)
        },
        next: function (e) {
          return O(e, 'nextSibling')
        },
        prev: function (e) {
          return O(e, 'previousSibling')
        },
        nextAll: function (e) {
          return h(e, 'nextSibling')
        },
        prevAll: function (e) {
          return h(e, 'previousSibling')
        },
        nextUntil: function (e, t, n) {
          return h(e, 'nextSibling', n)
        },
        prevUntil: function (e, t, n) {
          return h(e, 'previousSibling', n)
        },
        siblings: function (e) {
          return T((e.parentNode || {}).firstChild, e)
        },
        children: function (e) {
          return T(e.firstChild)
        },
        contents: function (e) {
          return null != e.contentDocument && r(e.contentDocument)
            ? e.contentDocument
            : (A(e, 'template') && (e = e.content || e),
              S.merge([], e.childNodes))
        },
      },
      function (r, i) {
        S.fn[r] = function (e, t) {
          var n = S.map(this, i, e)
          return (
            'Until' !== r.slice(-5) && (t = e),
            t && 'string' == typeof t && (n = S.filter(t, n)),
            1 < this.length &&
              (H[r] || S.uniqueSort(n), L.test(r) && n.reverse()),
            this.pushStack(n)
          )
        }
      }
    )
  var P = /[^\x20\t\r\n\f]+/g
  function R(e) {
    return e
  }
  function M(e) {
    throw e
  }
  function I(e, t, n, r) {
    var i
    try {
      e && m((i = e.promise))
        ? i && typeof i === 'function'
          ? i.call(e).done(t).fail(n)
          : e
        : e && m((i = e.then))
        ? i && typeof i === 'function'
          ? i.call(e, t, n)
          : e
        : t.apply(void 0, [e].slice(r))
    } catch (e) {
      n.apply(void 0, [e])
    }
  }
  ;(S.Callbacks = function (r) {
    var e, n
    r =
      'string' == typeof r
        ? ((e = r),
          (n = {}),
          S.each(e.match(P) || [], function (e, t) {
            n[t] = !0
          }),
          n)
        : S.extend({}, r)
    var i,
      t,
      o,
      a,
      s = [],
      u = [],
      l = -1,
      c = function () {
        for (a = a || r.once, o = i = !0; u.length; l = -1) {
          t = u.shift()
          while (++l < s.length)
            !1 === s[l].apply(t[0], t[1]) &&
              r.stopOnFalse &&
              ((l = s.length), (t = !1))
        }
        r.memory || (t = !1), (i = !1), a && (s = t ? [] : '')
      },
      f = {
        add: function () {
          return (
            s &&
              (t && !i && ((l = s.length - 1), u.push(t)),
              (function n(e) {
                S.each(e, function (e, t) {
                  m(t)
                    ? (r.unique && f.has(t)) || s.push(t)
                    : t && t.length && 'string' !== w(t) && n(t)
                })
              })(arguments),
              t && !i && c()),
            this
          )
        },
        remove: function () {
          return (
            S.each(arguments, function (e, t) {
              var n
              while (-1 < (n = S.inArray(t, s, n)))
                s.splice(n, 1), n <= l && l--
            }),
            this
          )
        },
        has: function (e) {
          return e ? -1 < S.inArray(e, s) : 0 < s.length
        },
        empty: function () {
          return s && (s = []), this
        },
        disable: function () {
          return (a = u = []), (s = t = ''), this
        },
        disabled: function () {
          return !s
        },
        lock: function () {
          return (a = u = []), t || i || (s = t = ''), this
        },
        locked: function () {
          return !!a
        },
        fireWith: function (e, t) {
          return (
            a ||
              ((t = [e, (t = t || []).slice ? t.slice() : t]),
              u.push(t),
              i || c()),
            this
          )
        },
        fire: function () {
          return f.fireWith(this, arguments), this
        },
        fired: function () {
          return !!o
        },
      }
    return f
  }),
    S.extend({
      Deferred: function (e) {
        var o = [
            [
              'notify',
              'progress',
              S.Callbacks('memory'),
              S.Callbacks('memory'),
              2,
            ],
            [
              'resolve',
              'done',
              S.Callbacks('once memory'),
              S.Callbacks('once memory'),
              0,
              'resolved',
            ],
            [
              'reject',
              'fail',
              S.Callbacks('once memory'),
              S.Callbacks('once memory'),
              1,
              'rejected',
            ],
          ],
          i = 'pending',
          a = {
            state: function () {
              return i
            },
            always: function () {
              return s.done(arguments).fail(arguments), this
            },
            catch: function (e) {
              return a.then(null, e)
            },
            pipe: function () {
              var i = arguments
              return S.Deferred(function (r) {
                S.each(o, function (e, t) {
                  var n = m(i[t[4]]) && i[t[4]]
                  s[t[1]](function () {
                    var e = n && n.apply(this, arguments)
                    e && m(e.promise)
                      ? e
                          .promise()
                          .progress(r.notify)
                          .done(r.resolve)
                          .fail(r.reject)
                      : r[t[0] + 'With'](this, n ? [e] : arguments)
                  })
                }),
                  (i = null)
              }).promise()
            },
            then: function (t, n, r) {
              var u = 0
              function l(i, o, a, s) {
                return function () {
                  var n = this,
                    r = arguments,
                    e = function () {
                      var e, t
                      if (!(i < u)) {
                        if ((e = a.apply(n, r)) === o.promise())
                          throw new TypeError('Thenable self-resolution')
                        ;(t =
                          e &&
                          ('object' == typeof e || 'function' == typeof e) &&
                          e.then),
                          m(t)
                            ? s
                              ? t.call(e, l(u, o, R, s), l(u, o, M, s))
                              : (u++,
                                t.call(
                                  e,
                                  l(u, o, R, s),
                                  l(u, o, M, s),
                                  l(u, o, R, o.notifyWith)
                                ))
                            : (a !== R && ((n = void 0), (r = [e])),
                              (s || o.resolveWith)(n, r))
                      }
                    },
                    t = s
                      ? e
                      : function () {
                          try {
                            e()
                          } catch (e) {
                            S.Deferred.exceptionHook &&
                              S.Deferred.exceptionHook(e, t.stackTrace),
                              u <= i + 1 &&
                                (a !== M && ((n = void 0), (r = [e])),
                                o.rejectWith(n, r))
                          }
                        }
                  i
                    ? t()
                    : (S.Deferred.getStackHook &&
                        (t.stackTrace = S.Deferred.getStackHook()),
                      C.setTimeout(t))
                }
              }
              return S.Deferred(function (e) {
                o[0][3].add(l(0, e, m(r) ? r : R, e.notifyWith)),
                  o[1][3].add(l(0, e, m(t) ? t : R)),
                  o[2][3].add(l(0, e, m(n) ? n : M))
              }).promise()
            },
            promise: function (e) {
              return null != e ? S.extend(e, a) : a
            },
          },
          s = {}
        return (
          S.each(o, function (e, t) {
            var n = t[2],
              r = t[5]
            ;(a[t[1]] = n.add),
              r &&
                n.add(
                  function () {
                    i = r
                  },
                  o[3 - e][2].disable,
                  o[3 - e][3].disable,
                  o[0][2].lock,
                  o[0][3].lock
                ),
              n.add(t[3].fire),
              (s[t[0]] = function () {
                return (
                  s[t[0] + 'With'](this === s ? void 0 : this, arguments), this
                )
              }),
              (s[t[0] + 'With'] = n.fireWith)
          }),
          a.promise(s),
          e && e.call(s, s),
          s
        )
      },
      when: function (e) {
        var n = arguments.length,
          t = n,
          r = Array(t),
          i = s.call(arguments),
          o = S.Deferred(),
          a = function (t) {
            return function (e) {
              ;(r[t] = this),
                (i[t] = 1 < arguments.length ? s.call(arguments) : e),
                --n || o.resolveWith(r, i)
            }
          }
        if (
          n <= 1 &&
          (I(e, o.done(a(t)).resolve, o.reject, !n),
          'pending' === o.state() || m(i[t] && i[t].then))
        )
          return o.then()
        while (t--) I(i[t], a(t), o.reject)
        return o.promise()
      },
    })
  var W = /^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/
  ;(S.Deferred.exceptionHook = function (e, t) {
    C.console &&
      C.console.warn &&
      e &&
      W.test(e.name) &&
      C.console.warn('jQuery.Deferred exception: ' + e.message, e.stack, t)
  }),
    (S.readyException = function (e) {
      C.setTimeout(function () {
        throw e
      })
    })
  var F = S.Deferred()
  function B() {
    E.removeEventListener('DOMContentLoaded', B),
      C.removeEventListener('load', B),
      S.ready()
  }
  ;(S.fn.ready = function (e) {
    return (
      F.then(e)['catch'](function (e) {
        S.readyException(e)
      }),
      this
    )
  }),
    S.extend({
      isReady: !1,
      readyWait: 1,
      ready: function (e) {
        ;(!0 === e ? --S.readyWait : S.isReady) ||
          ((S.isReady = !0) !== e && 0 < --S.readyWait) ||
          F.resolveWith(E, [S])
      },
    }),
    (S.ready.then = F.then),
    'complete' === E.readyState ||
    ('loading' !== E.readyState && !E.documentElement.doScroll)
      ? C.setTimeout(S.ready)
      : (E.addEventListener('DOMContentLoaded', B),
        C.addEventListener('load', B))
  var $ = function (e, t, n, r, i, o, a) {
      var s = 0,
        u = e.length,
        l = null == n
      if ('object' === w(n)) for (s in ((i = !0), n)) $(e, t, s, n[s], !0, o, a)
      else if (
        void 0 !== r &&
        ((i = !0),
        m(r) || (a = !0),
        l &&
          (a
            ? (t.call(e, r), (t = null))
            : ((l = t),
              (t = function (e, t, n) {
                return l.call(S(e), n)
              }))),
        t)
      )
        for (; s < u; s++) t(e[s], n, a ? r : r.call(e[s], s, t(e[s], n)))
      return i
        ? e
        : l
        ? t && typeof t === 'function'
          ? t.call(e)
          : e
        : u
        ? t(e[0], n)
        : o
    },
    _ = /^-ms-/,
    z = /-([a-z])/g
  function U(e, t) {
    return t.toUpperCase()
  }
  function X(e) {
    return e.replace(_, 'ms-').replace(z, U)
  }
  var V = function (e) {
    return 1 === e.nodeType || 9 === e.nodeType || !+e.nodeType
  }
  function G() {
    this.expando = S.expando + G.uid++
  }
  ;(G.uid = 1),
    (G.prototype = {
      cache: function (e) {
        var t = e[this.expando]
        return (
          t ||
            ((t = {}),
            V(e) &&
              (e.nodeType
                ? (e[this.expando] = t)
                : Object.defineProperty(e, this.expando, {
                    value: t,
                    configurable: !0,
                  }))),
          t
        )
      },
      set: function (e, t, n) {
        var r,
          i = this.cache(e)
        if ('string' == typeof t) i[X(t)] = n
        else for (r in t) i[X(r)] = t[r]
        return i
      },
      get: function (e, t) {
        return void 0 === t
          ? this.cache(e)
          : e[this.expando] && e[this.expando][X(t)]
      },
      access: function (e, t, n) {
        return void 0 === t || (t && 'string' == typeof t && void 0 === n)
          ? this.get(e, t)
          : (this.set(e, t, n), void 0 !== n ? n : t)
      },
      remove: function (e, t) {
        var n,
          r = e[this.expando]
        if (void 0 !== r) {
          if (void 0 !== t) {
            n = (t = Array.isArray(t)
              ? t.map(X)
              : (t = X(t)) in r
              ? [t]
              : t.match(P) || []).length
            while (n--) delete r[t[n]]
          }
          ;(void 0 === t || S.isEmptyObject(r)) &&
            (e.nodeType ? (e[this.expando] = void 0) : delete e[this.expando])
        }
      },
      hasData: function (e) {
        var t = e[this.expando]
        return void 0 !== t && !S.isEmptyObject(t)
      },
    })
  var Y = new G(),
    Q = new G(),
    J = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,
    K = /[A-Z]/g
  function Z(e, t, n) {
    var r, i
    if (void 0 === n && 1 === e.nodeType)
      if (
        ((r = 'data-' + t.replace(K, '-$&').toLowerCase()),
        'string' == typeof (n = e.getAttribute(r)))
      ) {
        try {
          n =
            'true' === (i = n) ||
            ('false' !== i &&
              ('null' === i
                ? null
                : i === +i + ''
                ? +i
                : J.test(i)
                ? JSON.parse(i)
                : i))
        } catch (e) {}
        Q.set(e, t, n)
      } else n = void 0
    return n
  }
  S.extend({
    hasData: function (e) {
      return Q.hasData(e) || Y.hasData(e)
    },
    data: function (e, t, n) {
      return Q.access(e, t, n)
    },
    removeData: function (e, t) {
      Q.remove(e, t)
    },
    _data: function (e, t, n) {
      return Y.access(e, t, n)
    },
    _removeData: function (e, t) {
      Y.remove(e, t)
    },
  }),
    S.fn.extend({
      data: function (n, e) {
        var t,
          r,
          i,
          o = this[0],
          a = o && o.attributes
        if (void 0 === n) {
          if (
            this.length &&
            ((i = Q.get(o)), 1 === o.nodeType && !Y.get(o, 'hasDataAttrs'))
          ) {
            t = a.length
            while (t--)
              a[t] &&
                0 === (r = a[t].name).indexOf('data-') &&
                ((r = X(r.slice(5))), Z(o, r, i[r]))
            Y.set(o, 'hasDataAttrs', !0)
          }
          return i
        }
        return 'object' == typeof n
          ? this.each(function () {
              Q.set(this, n)
            })
          : $(
              this,
              function (e) {
                var t
                if (o && void 0 === e)
                  return void 0 !== (t = Q.get(o, n))
                    ? t
                    : void 0 !== (t = Z(o, n))
                    ? t
                    : void 0
                this.each(function () {
                  Q.set(this, n, e)
                })
              },
              null,
              e,
              1 < arguments.length,
              null,
              !0
            )
      },
      removeData: function (e) {
        return this.each(function () {
          Q.remove(this, e)
        })
      },
    }),
    S.extend({
      queue: function (e, t, n) {
        var r
        if (e)
          return (
            (t = (t || 'fx') + 'queue'),
            (r = Y.get(e, t)),
            n &&
              (!r || Array.isArray(n)
                ? (r = Y.access(e, t, S.makeArray(n)))
                : r.push(n)),
            r || []
          )
      },
      dequeue: function (e, t) {
        t = t || 'fx'
        var n = S.queue(e, t),
          r = n.length,
          i = n.shift(),
          o = S._queueHooks(e, t)
        'inprogress' === i && ((i = n.shift()), r--),
          i &&
            ('fx' === t && n.unshift('inprogress'),
            delete o.stop,
            i && typeof i === 'function'
              ? i.call(
                  e,
                  function () {
                    S.dequeue(e, t)
                  },
                  o
                )
              : i),
          !r && o && o.empty.fire()
      },
      _queueHooks: function (e, t) {
        var n = t + 'queueHooks'
        return (
          Y.get(e, n) ||
          Y.access(e, n, {
            empty: S.Callbacks('once memory').add(function () {
              Y.remove(e, [t + 'queue', n])
            }),
          })
        )
      },
    }),
    S.fn.extend({
      queue: function (t, n) {
        var e = 2
        return (
          'string' != typeof t && ((n = t), (t = 'fx'), e--),
          arguments.length < e
            ? S.queue(this[0], t)
            : void 0 === n
            ? this
            : this.each(function () {
                var e = S.queue(this, t, n)
                S._queueHooks(this, t),
                  'fx' === t && 'inprogress' !== e[0] && S.dequeue(this, t)
              })
        )
      },
      dequeue: function (e) {
        return this.each(function () {
          S.dequeue(this, e)
        })
      },
      clearQueue: function (e) {
        return this.queue(e || 'fx', [])
      },
      promise: function (e, t) {
        var n,
          r = 1,
          i = S.Deferred(),
          o = this,
          a = this.length,
          s = function () {
            --r || i.resolveWith(o, [o])
          }
        'string' != typeof e && ((t = e), (e = void 0)), (e = e || 'fx')
        while (a--)
          (n = Y.get(o[a], e + 'queueHooks')) &&
            n.empty &&
            (r++, n.empty.add(s))
        return s(), i.promise(t)
      },
    })
  var ee = /[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,
    te = new RegExp('^(?:([+-])=|)(' + ee + ')([a-z%]*)$', 'i'),
    ne = ['Top', 'Right', 'Bottom', 'Left'],
    re = E.documentElement,
    ie = function (e) {
      return S.contains(e.ownerDocument, e)
    },
    oe = { composed: !0 }
  re.getRootNode &&
    (ie = function (e) {
      return (
        S.contains(e.ownerDocument, e) || e.getRootNode(oe) === e.ownerDocument
      )
    })
  var ae = function (e, t) {
    return (
      'none' === (e = t || e).style.display ||
      ('' === e.style.display && ie(e) && 'none' === S.css(e, 'display'))
    )
  }
  function se(e, t, n, r) {
    var i,
      o,
      a = 20,
      s = r
        ? function () {
            return r.cur()
          }
        : function () {
            return S.css(e, t, '')
          },
      u = s(),
      l = (n && n[3]) || (S.cssNumber[t] ? '' : 'px'),
      c =
        e.nodeType &&
        (S.cssNumber[t] || ('px' !== l && +u)) &&
        te.exec(S.css(e, t))
    if (c && c[3] !== l) {
      ;(u /= 2), (l = l || c[3]), (c = +u || 1)
      while (a--)
        S.style(e, t, c + l),
          (1 - o) * (1 - (o = s() / u || 0.5)) <= 0 && (a = 0),
          (c /= o)
      ;(c *= 2), S.style(e, t, c + l), (n = n || [])
    }
    return (
      n &&
        ((c = +c || +u || 0),
        (i = n[1] ? c + (n[1] + 1) * n[2] : +n[2]),
        r && ((r.unit = l), (r.start = c), (r.end = i))),
      i
    )
  }
  var ue = {}
  function le(e, t) {
    for (var n, r, i, o, a, s, u, l = [], c = 0, f = e.length; c < f; c++)
      (r = e[c]).style &&
        ((n = r.style.display),
        t
          ? ('none' === n &&
              ((l[c] = Y.get(r, 'display') || null),
              l[c] || (r.style.display = '')),
            '' === r.style.display &&
              ae(r) &&
              (l[c] =
                ((u = a = o = void 0),
                (a = (i = r).ownerDocument),
                (s = i.nodeName),
                (u = ue[s]) ||
                  ((o = a.body.appendChild(a.createElement(s))),
                  (u = S.css(o, 'display')),
                  o.parentNode.removeChild(o),
                  'none' === u && (u = 'block'),
                  (ue[s] = u)))))
          : 'none' !== n && ((l[c] = 'none'), Y.set(r, 'display', n)))
    for (c = 0; c < f; c++) null != l[c] && (e[c].style.display = l[c])
    return e
  }
  S.fn.extend({
    show: function () {
      return le(this, !0)
    },
    hide: function () {
      return le(this)
    },
    toggle: function (e) {
      return 'boolean' == typeof e
        ? e
          ? this.show()
          : this.hide()
        : this.each(function () {
            ae(this) ? S(this).show() : S(this).hide()
          })
    },
  })
  var ce,
    fe,
    pe = /^(?:checkbox|radio)$/i,
    de = /<([a-z][^\/\0>\x20\t\r\n\f]*)/i,
    he = /^$|^module$|\/(?:java|ecma)script/i
  ;(ce = E.createDocumentFragment().appendChild(E.createElement('div'))),
    (fe = E.createElement('input')).setAttribute('type', 'radio'),
    fe.setAttribute('checked', 'checked'),
    fe.setAttribute('name', 't'),
    ce.appendChild(fe),
    (y.checkClone = ce.cloneNode(!0).cloneNode(!0).lastChild.checked),
    (ce.innerHTML = '<textarea>x</textarea>'),
    (y.noCloneChecked = !!ce.cloneNode(!0).lastChild.defaultValue),
    (ce.innerHTML = '<option></option>'),
    (y.option = !!ce.lastChild)
  var ge = {
    thead: [1, '<table>', '</table>'],
    col: [2, '<table><colgroup>', '</colgroup></table>'],
    tr: [2, '<table><tbody>', '</tbody></table>'],
    td: [3, '<table><tbody><tr>', '</tr></tbody></table>'],
    _default: [0, '', ''],
  }
  function ve(e, t) {
    var n
    return (
      (n =
        'undefined' != typeof e.getElementsByTagName
          ? e.getElementsByTagName(t || '*')
          : 'undefined' != typeof e.querySelectorAll
          ? e.querySelectorAll(t || '*')
          : []),
      void 0 === t || (t && A(e, t)) ? S.merge([e], n) : n
    )
  }
  function ye(e, t) {
    for (var n = 0, r = e.length; n < r; n++)
      Y.set(e[n], 'globalEval', !t || Y.get(t[n], 'globalEval'))
  }
  ;(ge.tbody = ge.tfoot = ge.colgroup = ge.caption = ge.thead),
    (ge.th = ge.td),
    y.option ||
      (ge.optgroup = ge.option =
        [1, "<select multiple='multiple'>", '</select>'])
  var me = /<|&#?\w+;/
  function xe(e, t, n, r, i) {
    for (
      var o,
        a,
        s,
        u,
        l,
        c,
        f = t.createDocumentFragment(),
        p = [],
        d = 0,
        h = e.length;
      d < h;
      d++
    )
      if ((o = e[d]) || 0 === o)
        if ('object' === w(o)) S.merge(p, o.nodeType ? [o] : o)
        else if (me.test(o)) {
          ;(a = a || f.appendChild(t.createElement('div'))),
            (s = (de.exec(o) || ['', ''])[1].toLowerCase()),
            (u = ge[s] || ge._default),
            (a.innerHTML = u[1] + S.htmlPrefilter(o) + u[2]),
            (c = u[0])
          while (c--) a = a.lastChild
          S.merge(p, a.childNodes), ((a = f.firstChild).textContent = '')
        } else p.push(t.createTextNode(o))
    ;(f.textContent = ''), (d = 0)
    while ((o = p[d++]))
      if (r && -1 < S.inArray(o, r)) i && i.push(o)
      else if (
        ((l = ie(o)), (a = ve(f.appendChild(o), 'script')), l && ye(a), n)
      ) {
        c = 0
        while ((o = a[c++])) he.test(o.type || '') && n.push(o)
      }
    return f
  }
  var be = /^key/,
    we = /^(?:mouse|pointer|contextmenu|drag|drop)|click/,
    Te = /^([^.]*)(?:\.(.+)|)/
  function Ce() {
    return !0
  }
  function Ee() {
    return !1
  }
  function Se(e, t) {
    return (
      (e ===
        (function () {
          try {
            return E.activeElement
          } catch (e) {}
        })()) ==
      ('focus' === t)
    )
  }
  function ke(e, t, n, r, i, o) {
    var a, s
    if ('object' == typeof t) {
      for (s in ('string' != typeof n && ((r = r || n), (n = void 0)), t))
        ke(e, s, n, r, t[s], o)
      return e
    }
    if (
      (null == r && null == i
        ? ((i = n), (r = n = void 0))
        : null == i &&
          ('string' == typeof n
            ? ((i = r), (r = void 0))
            : ((i = r), (r = n), (n = void 0))),
      !1 === i)
    )
      i = Ee
    else if (!i) return e
    return (
      1 === o &&
        ((a = i),
        ((i = function (e) {
          return S().off(e), a.apply(this, arguments)
        }).guid = a.guid || (a.guid = S.guid++))),
      e.each(function () {
        S.event.add(this, t, i, r, n)
      })
    )
  }
  function Ae(e, i, o) {
    o
      ? (Y.set(e, i, !1),
        S.event.add(e, i, {
          namespace: !1,
          handler: function (e) {
            var t,
              n,
              r = Y.get(this, i)
            if (1 & e.isTrigger && this[i]) {
              if (r.length)
                (S.event.special[i] || {}).delegateType && e.stopPropagation()
              else if (
                ((r = s.call(arguments)),
                Y.set(this, i, r),
                (t = o(this, i)),
                this[i](),
                r !== (n = Y.get(this, i)) || t ? Y.set(this, i, !1) : (n = {}),
                r !== n)
              )
                return e.stopImmediatePropagation(), e.preventDefault(), n.value
            } else
              r.length &&
                (Y.set(this, i, {
                  value: S.event.trigger(
                    S.extend(r[0], S.Event.prototype),
                    r.slice(1),
                    this
                  ),
                }),
                e.stopImmediatePropagation())
          },
        }))
      : void 0 === Y.get(e, i) && S.event.add(e, i, Ce)
  }
  ;(S.event = {
    global: {},
    add: function (t, e, n, r, i) {
      var o,
        a,
        s,
        u,
        l,
        c,
        f,
        p,
        d,
        h,
        g,
        v = Y.get(t)
      if (V(t)) {
        n.handler && ((n = (o = n).handler), (i = o.selector)),
          i && S.find.matchesSelector(re, i),
          n.guid || (n.guid = S.guid++),
          (u = v.events) || (u = v.events = Object.create(null)),
          (a = v.handle) ||
            (a = v.handle =
              function (e) {
                return 'undefined' != typeof S && S.event.triggered !== e.type
                  ? S.event.dispatch.apply(t, arguments)
                  : void 0
              }),
          (l = (e = (e || '').match(P) || ['']).length)
        while (l--)
          (d = g = (s = Te.exec(e[l]) || [])[1]),
            (h = (s[2] || '').split('.').sort()),
            d &&
              ((f = S.event.special[d] || {}),
              (d = (i ? f.delegateType : f.bindType) || d),
              (f = S.event.special[d] || {}),
              (c = S.extend(
                {
                  type: d,
                  origType: g,
                  data: r,
                  handler: n,
                  guid: n.guid,
                  selector: i,
                  needsContext: i && S.expr.match.needsContext.test(i),
                  namespace: h.join('.'),
                },
                o
              )),
              (p = u[d]) ||
                (((p = u[d] = []).delegateCount = 0),
                (f.setup && !1 !== f.setup.call(t, r, h, a)) ||
                  (t.addEventListener && t.addEventListener(d, a))),
              f.add &&
                (f.add.call(t, c), c.handler.guid || (c.handler.guid = n.guid)),
              i ? p.splice(p.delegateCount++, 0, c) : p.push(c),
              (S.event.global[d] = !0))
      }
    },
    remove: function (e, t, n, r, i) {
      var o,
        a,
        s,
        u,
        l,
        c,
        f,
        p,
        d,
        h,
        g,
        v = Y.hasData(e) && Y.get(e)
      if (v && (u = v.events)) {
        l = (t = (t || '').match(P) || ['']).length
        while (l--)
          if (
            ((d = g = (s = Te.exec(t[l]) || [])[1]),
            (h = (s[2] || '').split('.').sort()),
            d)
          ) {
            ;(f = S.event.special[d] || {}),
              (p = u[(d = (r ? f.delegateType : f.bindType) || d)] || []),
              (s =
                s[2] &&
                new RegExp('(^|\\.)' + h.join('\\.(?:.*\\.|)') + '(\\.|$)')),
              (a = o = p.length)
            while (o--)
              (c = p[o]),
                (!i && g !== c.origType) ||
                  (n && n.guid !== c.guid) ||
                  (s && !s.test(c.namespace)) ||
                  (r && r !== c.selector && ('**' !== r || !c.selector)) ||
                  (p.splice(o, 1),
                  c.selector && p.delegateCount--,
                  f.remove && f.remove.call(e, c))
            a &&
              !p.length &&
              ((f.teardown && !1 !== f.teardown.call(e, h, v.handle)) ||
                S.removeEvent(e, d, v.handle),
              delete u[d])
          } else for (d in u) S.event.remove(e, d + t[l], n, r, !0)
        S.isEmptyObject(u) && Y.remove(e, 'handle events')
      }
    },
    dispatch: function (e) {
      var t,
        n,
        r,
        i,
        o,
        a,
        s = new Array(arguments.length),
        u = S.event.fix(e),
        l = (Y.get(this, 'events') || Object.create(null))[u.type] || [],
        c = S.event.special[u.type] || {}
      for (s[0] = u, t = 1; t < arguments.length; t++) s[t] = arguments[t]
      if (
        ((u.delegateTarget = this),
        !c.preDispatch || !1 !== c.preDispatch.call(this, u))
      ) {
        ;(a = S.event.handlers.call(this, u, l)), (t = 0)
        while ((i = a[t++]) && !u.isPropagationStopped()) {
          ;(u.currentTarget = i.elem), (n = 0)
          while ((o = i.handlers[n++]) && !u.isImmediatePropagationStopped())
            (u.rnamespace &&
              !1 !== o.namespace &&
              !u.rnamespace.test(o.namespace)) ||
              ((u.handleObj = o),
              (u.data = o.data),
              void 0 !==
                (r = (
                  (S.event.special[o.origType] || {}).handle || o.handler
                ).apply(i.elem, s)) &&
                !1 === (u.result = r) &&
                (u.preventDefault(), u.stopPropagation()))
        }
        return c.postDispatch && c.postDispatch.call(this, u), u.result
      }
    },
    handlers: function (e, t) {
      var n,
        r,
        i,
        o,
        a,
        s = [],
        u = t.delegateCount,
        l = e.target
      if (u && l.nodeType && !('click' === e.type && 1 <= e.button))
        for (; l !== this; l = l.parentNode || this)
          if (1 === l.nodeType && ('click' !== e.type || !0 !== l.disabled)) {
            for (o = [], a = {}, n = 0; n < u; n++)
              void 0 === a[(i = (r = t[n]).selector + ' ')] &&
                (a[i] = r.needsContext
                  ? -1 < S(i, this).index(l)
                  : S.find(i, this, null, [l]).length),
                a[i] && o.push(r)
            o.length && s.push({ elem: l, handlers: o })
          }
      return (
        (l = this), u < t.length && s.push({ elem: l, handlers: t.slice(u) }), s
      )
    },
    addProp: function (t, e) {
      Object.defineProperty(S.Event.prototype, t, {
        enumerable: !0,
        configurable: !0,
        get: m(e)
          ? function () {
              if (this.originalEvent) return e(this.originalEvent)
            }
          : function () {
              if (this.originalEvent) return this.originalEvent[t]
            },
        set: function (e) {
          Object.defineProperty(this, t, {
            enumerable: !0,
            configurable: !0,
            writable: !0,
            value: e,
          })
        },
      })
    },
    fix: function (e) {
      return e[S.expando] ? e : new S.Event(e)
    },
    special: {
      load: { noBubble: !0 },
      click: {
        setup: function (e) {
          var t = this || e
          return (
            pe.test(t.type) && t.click && A(t, 'input') && Ae(t, 'click', Ce),
            !1
          )
        },
        trigger: function (e) {
          var t = this || e
          return (
            pe.test(t.type) && t.click && A(t, 'input') && Ae(t, 'click'), !0
          )
        },
        _default: function (e) {
          var t = e.target
          return (
            (pe.test(t.type) &&
              t.click &&
              A(t, 'input') &&
              Y.get(t, 'click')) ||
            A(t, 'a')
          )
        },
      },
      beforeunload: {
        postDispatch: function (e) {
          void 0 !== e.result &&
            e.originalEvent &&
            (e.originalEvent.returnValue = e.result)
        },
      },
    },
  }),
    (S.removeEvent = function (e, t, n) {
      e.removeEventListener && e.removeEventListener(t, n)
    }),
    (S.Event = function (e, t) {
      if (!(this instanceof S.Event)) return new S.Event(e, t)
      e && e.type
        ? ((this.originalEvent = e),
          (this.type = e.type),
          (this.isDefaultPrevented =
            e.defaultPrevented ||
            (void 0 === e.defaultPrevented && !1 === e.returnValue)
              ? Ce
              : Ee),
          (this.target =
            e.target && 3 === e.target.nodeType
              ? e.target.parentNode
              : e.target),
          (this.currentTarget = e.currentTarget),
          (this.relatedTarget = e.relatedTarget))
        : (this.type = e),
        t && S.extend(this, t),
        (this.timeStamp = (e && e.timeStamp) || Date.now()),
        (this[S.expando] = !0)
    }),
    (S.Event.prototype = {
      constructor: S.Event,
      isDefaultPrevented: Ee,
      isPropagationStopped: Ee,
      isImmediatePropagationStopped: Ee,
      isSimulated: !1,
      preventDefault: function () {
        var e = this.originalEvent
        ;(this.isDefaultPrevented = Ce),
          e && !this.isSimulated && e.preventDefault()
      },
      stopPropagation: function () {
        var e = this.originalEvent
        ;(this.isPropagationStopped = Ce),
          e && !this.isSimulated && e.stopPropagation()
      },
      stopImmediatePropagation: function () {
        var e = this.originalEvent
        ;(this.isImmediatePropagationStopped = Ce),
          e && !this.isSimulated && e.stopImmediatePropagation(),
          this.stopPropagation()
      },
    }),
    S.each(
      {
        altKey: !0,
        bubbles: !0,
        cancelable: !0,
        changedTouches: !0,
        ctrlKey: !0,
        detail: !0,
        eventPhase: !0,
        metaKey: !0,
        pageX: !0,
        pageY: !0,
        shiftKey: !0,
        view: !0,
        char: !0,
        code: !0,
        charCode: !0,
        key: !0,
        keyCode: !0,
        button: !0,
        buttons: !0,
        clientX: !0,
        clientY: !0,
        offsetX: !0,
        offsetY: !0,
        pointerId: !0,
        pointerType: !0,
        screenX: !0,
        screenY: !0,
        targetTouches: !0,
        toElement: !0,
        touches: !0,
        which: function (e) {
          var t = e.button
          return null == e.which && be.test(e.type)
            ? null != e.charCode
              ? e.charCode
              : e.keyCode
            : !e.which && void 0 !== t && we.test(e.type)
            ? 1 & t
              ? 1
              : 2 & t
              ? 3
              : 4 & t
              ? 2
              : 0
            : e.which
        },
      },
      S.event.addProp
    ),
    S.each({ focus: 'focusin', blur: 'focusout' }, function (e, t) {
      S.event.special[e] = {
        setup: function () {
          return Ae(this, e, Se), !1
        },
        trigger: function () {
          return Ae(this, e), !0
        },
        delegateType: t,
      }
    }),
    S.each(
      {
        mouseenter: 'mouseover',
        mouseleave: 'mouseout',
        pointerenter: 'pointerover',
        pointerleave: 'pointerout',
      },
      function (e, i) {
        S.event.special[e] = {
          delegateType: i,
          bindType: i,
          handle: function (e) {
            var t,
              n = e.relatedTarget,
              r = e.handleObj
            return (
              (n && (n === this || S.contains(this, n))) ||
                ((e.type = r.origType),
                (t = r.handler.apply(this, arguments)),
                (e.type = i)),
              t
            )
          },
        }
      }
    ),
    S.fn.extend({
      on: function (e, t, n, r) {
        return ke(this, e, t, n, r)
      },
      one: function (e, t, n, r) {
        return ke(this, e, t, n, r, 1)
      },
      off: function (e, t, n) {
        var r, i
        if (e && e.preventDefault && e.handleObj)
          return (
            (r = e.handleObj),
            S(e.delegateTarget).off(
              r.namespace ? r.origType + '.' + r.namespace : r.origType,
              r.selector,
              r.handler
            ),
            this
          )
        if ('object' == typeof e) {
          for (i in e) this.off(i, t, e[i])
          return this
        }
        return (
          (!1 !== t && 'function' != typeof t) || ((n = t), (t = void 0)),
          !1 === n && (n = Ee),
          this.each(function () {
            S.event.remove(this, e, n, t)
          })
        )
      },
    })
  var Ne = /<script|<style|<link/i,
    De = /checked\s*(?:[^=]|=\s*.checked.)/i,
    je = /^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g
  function qe(e, t) {
    return (
      (A(e, 'table') &&
        A(11 !== t.nodeType ? t : t.firstChild, 'tr') &&
        S(e).children('tbody')[0]) ||
      e
    )
  }
  function Le(e) {
    return (e.type = (null !== e.getAttribute('type')) + '/' + e.type), e
  }
  function He(e) {
    return (
      'true/' === (e.type || '').slice(0, 5)
        ? (e.type = e.type.slice(5))
        : e.removeAttribute('type'),
      e
    )
  }
  function Oe(e, t) {
    var n, r, i, o, a, s
    if (1 === t.nodeType) {
      if (Y.hasData(e) && (s = Y.get(e).events))
        for (i in (Y.remove(t, 'handle events'), s))
          for (n = 0, r = s[i].length; n < r; n++) S.event.add(t, i, s[i][n])
      Q.hasData(e) && ((o = Q.access(e)), (a = S.extend({}, o)), Q.set(t, a))
    }
  }
  function Pe(n, r, i, o) {
    r = g(r)
    var e,
      t,
      a,
      s,
      u,
      l,
      c = 0,
      f = n.length,
      p = f - 1,
      d = r[0],
      h = m(d)
    if (h || (1 < f && 'string' == typeof d && !y.checkClone && De.test(d)))
      return n.each(function (e) {
        var t = n.eq(e)
        h && (r[0] = d.call(this, e, t.html())), Pe(t, r, i, o)
      })
    if (
      f &&
      ((t = (e = xe(r, n[0].ownerDocument, !1, n, o)).firstChild),
      1 === e.childNodes.length && (e = t),
      t || o)
    ) {
      for (s = (a = S.map(ve(e, 'script'), Le)).length; c < f; c++)
        (u = e),
          c !== p &&
            ((u = S.clone(u, !0, !0)), s && S.merge(a, ve(u, 'script'))),
          i && typeof i === 'function' ? i.call(n[c], u, c) : n[c]
      if (s)
        for (l = a[a.length - 1].ownerDocument, S.map(a, He), c = 0; c < s; c++)
          (u = a[c]),
            he.test(u.type || '') &&
              !Y.access(u, 'globalEval') &&
              S.contains(l, u) &&
              (u.src && 'module' !== (u.type || '').toLowerCase()
                ? S._evalUrl &&
                  !u.noModule &&
                  S._evalUrl(
                    u.src,
                    { nonce: u.nonce || u.getAttribute('nonce') },
                    l
                  )
                : b(u.textContent.replace(je, ''), u, l))
    }
    return n
  }
  function Re(e, t, n) {
    for (var r, i = t ? S.filter(t, e) : e, o = 0; null != (r = i[o]); o++)
      n || 1 !== r.nodeType || S.cleanData(ve(r)),
        r.parentNode &&
          (n && ie(r) && ye(ve(r, 'script')), r.parentNode.removeChild(r))
    return e
  }
  S.extend({
    htmlPrefilter: function (e) {
      return e
    },
    clone: function (e, t, n) {
      var r,
        i,
        o,
        a,
        s,
        u,
        l,
        c = e.cloneNode(!0),
        f = ie(e)
      if (
        !(
          y.noCloneChecked ||
          (1 !== e.nodeType && 11 !== e.nodeType) ||
          S.isXMLDoc(e)
        )
      )
        for (a = ve(c), r = 0, i = (o = ve(e)).length; r < i; r++)
          (s = o[r]),
            (u = a[r]),
            void 0,
            'input' === (l = u.nodeName.toLowerCase()) && pe.test(s.type)
              ? (u.checked = s.checked)
              : ('input' !== l && 'textarea' !== l) ||
                (u.defaultValue = s.defaultValue)
      if (t)
        if (n)
          for (o = o || ve(e), a = a || ve(c), r = 0, i = o.length; r < i; r++)
            Oe(o[r], a[r])
        else Oe(e, c)
      return 0 < (a = ve(c, 'script')).length && ye(a, !f && ve(e, 'script')), c
    },
    cleanData: function (e) {
      for (var t, n, r, i = S.event.special, o = 0; void 0 !== (n = e[o]); o++)
        if (V(n)) {
          if ((t = n[Y.expando])) {
            if (t.events)
              for (r in t.events)
                i[r] ? S.event.remove(n, r) : S.removeEvent(n, r, t.handle)
            n[Y.expando] = void 0
          }
          n[Q.expando] && (n[Q.expando] = void 0)
        }
    },
  }),
    S.fn.extend({
      detach: function (e) {
        return Re(this, e, !0)
      },
      remove: function (e) {
        return Re(this, e)
      },
      text: function (e) {
        return $(
          this,
          function (e) {
            return void 0 === e
              ? S.text(this)
              : this.empty().each(function () {
                  ;(1 !== this.nodeType &&
                    11 !== this.nodeType &&
                    9 !== this.nodeType) ||
                    (this.textContent = e)
                })
          },
          null,
          e,
          arguments.length
        )
      },
      append: function () {
        return Pe(this, arguments, function (e) {
          ;(1 !== this.nodeType &&
            11 !== this.nodeType &&
            9 !== this.nodeType) ||
            qe(this, e).appendChild(e)
        })
      },
      prepend: function () {
        return Pe(this, arguments, function (e) {
          if (
            1 === this.nodeType ||
            11 === this.nodeType ||
            9 === this.nodeType
          ) {
            var t = qe(this, e)
            t.insertBefore(e, t.firstChild)
          }
        })
      },
      before: function () {
        return Pe(this, arguments, function (e) {
          this.parentNode && this.parentNode.insertBefore(e, this)
        })
      },
      after: function () {
        return Pe(this, arguments, function (e) {
          this.parentNode && this.parentNode.insertBefore(e, this.nextSibling)
        })
      },
      empty: function () {
        for (var e, t = 0; null != (e = this[t]); t++)
          1 === e.nodeType && (S.cleanData(ve(e, !1)), (e.textContent = ''))
        return this
      },
      clone: function (e, t) {
        return (
          (e = null != e && e),
          (t = null == t ? e : t),
          this.map(function () {
            return S.clone(this, e, t)
          })
        )
      },
      html: function (e) {
        return $(
          this,
          function (e) {
            var t = this[0] || {},
              n = 0,
              r = this.length
            if (void 0 === e && 1 === t.nodeType) return t.innerHTML
            if (
              'string' == typeof e &&
              !Ne.test(e) &&
              !ge[(de.exec(e) || ['', ''])[1].toLowerCase()]
            ) {
              e = S.htmlPrefilter(e)
              try {
                for (; n < r; n++)
                  1 === (t = this[n] || {}).nodeType &&
                    (S.cleanData(ve(t, !1)), (t.innerHTML = e))
                t = 0
              } catch (e) {}
            }
            t && this.empty().append(e)
          },
          null,
          e,
          arguments.length
        )
      },
      replaceWith: function () {
        var n = []
        return Pe(
          this,
          arguments,
          function (e) {
            var t = this.parentNode
            S.inArray(this, n) < 0 &&
              (S.cleanData(ve(this)), t && t.replaceChild(e, this))
          },
          n
        )
      },
    }),
    S.each(
      {
        appendTo: 'append',
        prependTo: 'prepend',
        insertBefore: 'before',
        insertAfter: 'after',
        replaceAll: 'replaceWith',
      },
      function (e, a) {
        S.fn[e] = function (e) {
          for (var t, n = [], r = S(e), i = r.length - 1, o = 0; o <= i; o++)
            (t = o === i ? this : this.clone(!0)),
              S(r[o])[a](t),
              u.apply(n, t.get())
          return this.pushStack(n)
        }
      }
    )
  var Me = new RegExp('^(' + ee + ')(?!px)[a-z%]+$', 'i'),
    Ie = function (e) {
      var t = e.ownerDocument.defaultView
      return (t && t.opener) || (t = C), t.getComputedStyle(e)
    },
    We = function (e, t, n) {
      var r,
        i,
        o = {}
      for (i in t) (o[i] = e.style[i]), (e.style[i] = t[i])
      for (i in ((r = n.call(e)), t)) e.style[i] = o[i]
      return r
    },
    Fe = new RegExp(ne.join('|'), 'i')
  function Be(e, t, n) {
    var r,
      i,
      o,
      a,
      s = e.style
    return (
      (n = n || Ie(e)) &&
        ('' !== (a = n.getPropertyValue(t) || n[t]) ||
          ie(e) ||
          (a = S.style(e, t)),
        !y.pixelBoxStyles() &&
          Me.test(a) &&
          Fe.test(t) &&
          ((r = s.width),
          (i = s.minWidth),
          (o = s.maxWidth),
          (s.minWidth = s.maxWidth = s.width = a),
          (a = n.width),
          (s.width = r),
          (s.minWidth = i),
          (s.maxWidth = o))),
      void 0 !== a ? a + '' : a
    )
  }
  function $e(e, t) {
    return {
      get: function () {
        if (!e()) return (this.get = t).apply(this, arguments)
        delete this.get
      },
    }
  }
  !(function () {
    function e() {
      if (l) {
        ;(u.style.cssText =
          'position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0'),
          (l.style.cssText =
            'position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%'),
          re.appendChild(u).appendChild(l)
        var e = C.getComputedStyle(l)
        ;(n = '1%' !== e.top),
          (s = 12 === t(e.marginLeft)),
          (l.style.right = '60%'),
          (o = 36 === t(e.right)),
          (r = 36 === t(e.width)),
          (l.style.position = 'absolute'),
          (i = 12 === t(l.offsetWidth / 3)),
          re.removeChild(u),
          (l = null)
      }
    }
    function t(e) {
      return Math.round(parseFloat(e))
    }
    var n,
      r,
      i,
      o,
      a,
      s,
      u = E.createElement('div'),
      l = E.createElement('div')
    l.style &&
      ((l.style.backgroundClip = 'content-box'),
      (l.cloneNode(!0).style.backgroundClip = ''),
      (y.clearCloneStyle = 'content-box' === l.style.backgroundClip),
      S.extend(y, {
        boxSizingReliable: function () {
          return e(), r
        },
        pixelBoxStyles: function () {
          return e(), o
        },
        pixelPosition: function () {
          return e(), n
        },
        reliableMarginLeft: function () {
          return e(), s
        },
        scrollboxSize: function () {
          return e(), i
        },
        reliableTrDimensions: function () {
          var e, t, n, r
          return (
            null == a &&
              ((e = E.createElement('table')),
              (t = E.createElement('tr')),
              (n = E.createElement('div')),
              (e.style.cssText = 'position:absolute;left:-11111px'),
              (t.style.height = '1px'),
              (n.style.height = '9px'),
              re.appendChild(e).appendChild(t).appendChild(n),
              (r = C.getComputedStyle(t)),
              (a = 3 < parseInt(r.height)),
              re.removeChild(e)),
            a
          )
        },
      }))
  })()
  var _e = ['Webkit', 'Moz', 'ms'],
    ze = E.createElement('div').style,
    Ue = {}
  function Xe(e) {
    var t = S.cssProps[e] || Ue[e]
    return (
      t ||
      (e in ze
        ? e
        : (Ue[e] =
            (function (e) {
              var t = e[0].toUpperCase() + e.slice(1),
                n = _e.length
              while (n--) if ((e = _e[n] + t) in ze) return e
            })(e) || e))
    )
  }
  var Ve = /^(none|table(?!-c[ea]).+)/,
    Ge = /^--/,
    Ye = { position: 'absolute', visibility: 'hidden', display: 'block' },
    Qe = { letterSpacing: '0', fontWeight: '400' }
  function Je(e, t, n) {
    var r = te.exec(t)
    return r ? Math.max(0, r[2] - (n || 0)) + (r[3] || 'px') : t
  }
  function Ke(e, t, n, r, i, o) {
    var a = 'width' === t ? 1 : 0,
      s = 0,
      u = 0
    if (n === (r ? 'border' : 'content')) return 0
    for (; a < 4; a += 2)
      'margin' === n && (u += S.css(e, n + ne[a], !0, i)),
        r
          ? ('content' === n && (u -= S.css(e, 'padding' + ne[a], !0, i)),
            'margin' !== n &&
              (u -= S.css(e, 'border' + ne[a] + 'Width', !0, i)))
          : ((u += S.css(e, 'padding' + ne[a], !0, i)),
            'padding' !== n
              ? (u += S.css(e, 'border' + ne[a] + 'Width', !0, i))
              : (s += S.css(e, 'border' + ne[a] + 'Width', !0, i)))
    return (
      !r &&
        0 <= o &&
        (u +=
          Math.max(
            0,
            Math.ceil(
              e['offset' + t[0].toUpperCase() + t.slice(1)] - o - u - s - 0.5
            )
          ) || 0),
      u
    )
  }
  function Ze(e, t, n) {
    var r = Ie(e),
      i =
        (!y.boxSizingReliable() || n) &&
        'border-box' === S.css(e, 'boxSizing', !1, r),
      o = i,
      a = Be(e, t, r),
      s = 'offset' + t[0].toUpperCase() + t.slice(1)
    if (Me.test(a)) {
      if (!n) return a
      a = 'auto'
    }
    return (
      ((!y.boxSizingReliable() && i) ||
        (!y.reliableTrDimensions() && A(e, 'tr')) ||
        'auto' === a ||
        (!parseFloat(a) && 'inline' === S.css(e, 'display', !1, r))) &&
        e.getClientRects().length &&
        ((i = 'border-box' === S.css(e, 'boxSizing', !1, r)),
        (o = s in e) && (a = e[s])),
      (a = parseFloat(a) || 0) +
        Ke(e, t, n || (i ? 'border' : 'content'), o, r, a) +
        'px'
    )
  }
  function et(e, t, n, r, i) {
    return new et.prototype.init(e, t, n, r, i)
  }
  S.extend({
    cssHooks: {
      opacity: {
        get: function (e, t) {
          if (t) {
            var n = Be(e, 'opacity')
            return '' === n ? '1' : n
          }
        },
      },
    },
    cssNumber: {
      animationIterationCount: !0,
      columnCount: !0,
      fillOpacity: !0,
      flexGrow: !0,
      flexShrink: !0,
      fontWeight: !0,
      gridArea: !0,
      gridColumn: !0,
      gridColumnEnd: !0,
      gridColumnStart: !0,
      gridRow: !0,
      gridRowEnd: !0,
      gridRowStart: !0,
      lineHeight: !0,
      opacity: !0,
      order: !0,
      orphans: !0,
      widows: !0,
      zIndex: !0,
      zoom: !0,
    },
    cssProps: {},
    style: function (e, t, n, r) {
      if (e && 3 !== e.nodeType && 8 !== e.nodeType && e.style) {
        var i,
          o,
          a,
          s = X(t),
          u = Ge.test(t),
          l = e.style
        if (
          (u || (t = Xe(s)), (a = S.cssHooks[t] || S.cssHooks[s]), void 0 === n)
        )
          return a && 'get' in a && void 0 !== (i = a.get(e, !1, r)) ? i : l[t]
        'string' === (o = typeof n) &&
          (i = te.exec(n)) &&
          i[1] &&
          ((n = se(e, t, i)), (o = 'number')),
          null != n &&
            n == n &&
            ('number' !== o ||
              u ||
              (n += (i && i[3]) || (S.cssNumber[s] ? '' : 'px')),
            y.clearCloneStyle ||
              '' !== n ||
              0 !== t.indexOf('background') ||
              (l[t] = 'inherit'),
            (a && 'set' in a && void 0 === (n = a.set(e, n, r))) ||
              (u ? l.setProperty(t, n) : (l[t] = n)))
      }
    },
    css: function (e, t, n, r) {
      var i,
        o,
        a,
        s = X(t)
      return (
        Ge.test(t) || (t = Xe(s)),
        (a = S.cssHooks[t] || S.cssHooks[s]) &&
          'get' in a &&
          (i = a.get(e, !0, n)),
        void 0 === i && (i = Be(e, t, r)),
        'normal' === i && t in Qe && (i = Qe[t]),
        '' === n || n
          ? ((o = parseFloat(i)), !0 === n || isFinite(o) ? o || 0 : i)
          : i
      )
    },
  }),
    S.each(['height', 'width'], function (e, u) {
      S.cssHooks[u] = {
        get: function (e, t, n) {
          if (t)
            return !Ve.test(S.css(e, 'display')) ||
              (e.getClientRects().length && e.getBoundingClientRect().width)
              ? Ze(e, u, n)
              : We(e, Ye, function () {
                  return Ze(e, u, n)
                })
        },
        set: function (e, t, n) {
          var r,
            i = Ie(e),
            o = !y.scrollboxSize() && 'absolute' === i.position,
            a = (o || n) && 'border-box' === S.css(e, 'boxSizing', !1, i),
            s = n ? Ke(e, u, n, a, i) : 0
          return (
            a &&
              o &&
              (s -= Math.ceil(
                e['offset' + u[0].toUpperCase() + u.slice(1)] -
                  parseFloat(i[u]) -
                  Ke(e, u, 'border', !1, i) -
                  0.5
              )),
            s &&
              (r = te.exec(t)) &&
              'px' !== (r[3] || 'px') &&
              ((e.style[u] = t), (t = S.css(e, u))),
            Je(0, t, s)
          )
        },
      }
    }),
    (S.cssHooks.marginLeft = $e(y.reliableMarginLeft, function (e, t) {
      if (t)
        return (
          (parseFloat(Be(e, 'marginLeft')) ||
            e.getBoundingClientRect().left -
              We(e, { marginLeft: 0 }, function () {
                return e.getBoundingClientRect().left
              })) + 'px'
        )
    })),
    S.each({ margin: '', padding: '', border: 'Width' }, function (i, o) {
      ;(S.cssHooks[i + o] = {
        expand: function (e) {
          for (
            var t = 0, n = {}, r = 'string' == typeof e ? e.split(' ') : [e];
            t < 4;
            t++
          )
            n[i + ne[t] + o] = r[t] || r[t - 2] || r[0]
          return n
        },
      }),
        'margin' !== i && (S.cssHooks[i + o].set = Je)
    }),
    S.fn.extend({
      css: function (e, t) {
        return $(
          this,
          function (e, t, n) {
            var r,
              i,
              o = {},
              a = 0
            if (Array.isArray(t)) {
              for (r = Ie(e), i = t.length; a < i; a++)
                o[t[a]] = S.css(e, t[a], !1, r)
              return o
            }
            return void 0 !== n ? S.style(e, t, n) : S.css(e, t)
          },
          e,
          t,
          1 < arguments.length
        )
      },
    }),
    (((S.Tween = et).prototype = {
      constructor: et,
      init: function (e, t, n, r, i, o) {
        ;(this.elem = e),
          (this.prop = n),
          (this.easing = i || S.easing._default),
          (this.options = t),
          (this.start = this.now = this.cur()),
          (this.end = r),
          (this.unit = o || (S.cssNumber[n] ? '' : 'px'))
      },
      cur: function () {
        var e = et.propHooks[this.prop]
        return e && e.get ? e.get(this) : et.propHooks._default.get(this)
      },
      run: function (e) {
        var t,
          n = et.propHooks[this.prop]
        return (
          this.options.duration
            ? (this.pos = t =
                S.easing[this.easing](
                  e,
                  this.options.duration * e,
                  0,
                  1,
                  this.options.duration
                ))
            : (this.pos = t = e),
          (this.now = (this.end - this.start) * t + this.start),
          this.options.step &&
            this.options.step.call(this.elem, this.now, this),
          n && n.set ? n.set(this) : et.propHooks._default.set(this),
          this
        )
      },
    }).init.prototype = et.prototype),
    ((et.propHooks = {
      _default: {
        get: function (e) {
          var t
          return 1 !== e.elem.nodeType ||
            (null != e.elem[e.prop] && null == e.elem.style[e.prop])
            ? e.elem[e.prop]
            : (t = S.css(e.elem, e.prop, '')) && 'auto' !== t
            ? t
            : 0
        },
        set: function (e) {
          S.fx.step[e.prop]
            ? S.fx.step[e.prop](e)
            : 1 !== e.elem.nodeType ||
              (!S.cssHooks[e.prop] && null == e.elem.style[Xe(e.prop)])
            ? (e.elem[e.prop] = e.now)
            : S.style(e.elem, e.prop, e.now + e.unit)
        },
      },
    }).scrollTop = et.propHooks.scrollLeft =
      {
        set: function (e) {
          e.elem.nodeType && e.elem.parentNode && (e.elem[e.prop] = e.now)
        },
      }),
    (S.easing = {
      linear: function (e) {
        return e
      },
      swing: function (e) {
        return 0.5 - Math.cos(e * Math.PI) / 2
      },
      _default: 'swing',
    }),
    (S.fx = et.prototype.init),
    (S.fx.step = {})
  var tt,
    nt,
    rt,
    it,
    ot = /^(?:toggle|show|hide)$/,
    at = /queueHooks$/
  function st() {
    nt &&
      (!1 === E.hidden && C.requestAnimationFrame
        ? C.requestAnimationFrame(st)
        : C.setTimeout(st, S.fx.interval),
      S.fx.tick())
  }
  function ut() {
    return (
      C.setTimeout(function () {
        tt = void 0
      }),
      (tt = Date.now())
    )
  }
  function lt(e, t) {
    var n,
      r = 0,
      i = { height: e }
    for (t = t ? 1 : 0; r < 4; r += 2 - t)
      i['margin' + (n = ne[r])] = i['padding' + n] = e
    return t && (i.opacity = i.width = e), i
  }
  function ct(e, t, n) {
    for (
      var r,
        i = (ft.tweeners[t] || []).concat(ft.tweeners['*']),
        o = 0,
        a = i.length;
      o < a;
      o++
    )
      if ((r = i[o].call(n, t, e))) return r
  }
  function ft(o, e, t) {
    var n,
      a,
      r = 0,
      i = ft.prefilters.length,
      s = S.Deferred().always(function () {
        delete u.elem
      }),
      u = function () {
        if (a) return !1
        for (
          var e = tt || ut(),
            t = Math.max(0, l.startTime + l.duration - e),
            n = 1 - (t / l.duration || 0),
            r = 0,
            i = l.tweens.length;
          r < i;
          r++
        )
          l.tweens[r].run(n)
        return (
          s.notifyWith(o, [l, n, t]),
          n < 1 && i
            ? t
            : (i || s.notifyWith(o, [l, 1, 0]), s.resolveWith(o, [l]), !1)
        )
      },
      l = s.promise({
        elem: o,
        props: S.extend({}, e),
        opts: S.extend(!0, { specialEasing: {}, easing: S.easing._default }, t),
        originalProperties: e,
        originalOptions: t,
        startTime: tt || ut(),
        duration: t.duration,
        tweens: [],
        createTween: function (e, t) {
          var n = S.Tween(
            o,
            l.opts,
            e,
            t,
            l.opts.specialEasing[e] || l.opts.easing
          )
          return l.tweens.push(n), n
        },
        stop: function (e) {
          var t = 0,
            n = e ? l.tweens.length : 0
          if (a) return this
          for (a = !0; t < n; t++) l.tweens[t].run(1)
          return (
            e
              ? (s.notifyWith(o, [l, 1, 0]), s.resolveWith(o, [l, e]))
              : s.rejectWith(o, [l, e]),
            this
          )
        },
      }),
      c = l.props
    for (
      !(function (e, t) {
        var n, r, i, o, a
        for (n in e)
          if (
            ((i = t[(r = X(n))]),
            (o = e[n]),
            Array.isArray(o) && ((i = o[1]), (o = e[n] = o[0])),
            n !== r && ((e[r] = o), delete e[n]),
            (a = S.cssHooks[r]) && ('expand' in a))
          )
            for (n in ((o = a.expand(o)), delete e[r], o))
              (n in e) || ((e[n] = o[n]), (t[n] = i))
          else t[r] = i
      })(c, l.opts.specialEasing);
      r < i;
      r++
    )
      if ((n = ft.prefilters[r].call(l, o, c, l.opts)))
        return (
          m(n.stop) &&
            (S._queueHooks(l.elem, l.opts.queue).stop = n.stop.bind(n)),
          n
        )
    return (
      S.map(c, ct, l),
      m(l.opts.start) && l.opts.start.call(o, l),
      l
        .progress(l.opts.progress)
        .done(l.opts.done, l.opts.complete)
        .fail(l.opts.fail)
        .always(l.opts.always),
      S.fx.timer(S.extend(u, { elem: o, anim: l, queue: l.opts.queue })),
      l
    )
  }
  ;(S.Animation = S.extend(ft, {
    tweeners: {
      '*': [
        function (e, t) {
          var n = this.createTween(e, t)
          return se(n.elem, e, te.exec(t), n), n
        },
      ],
    },
    tweener: function (e, t) {
      m(e) ? ((t = e), (e = ['*'])) : (e = e.match(P))
      for (var n, r = 0, i = e.length; r < i; r++)
        (n = e[r]),
          (ft.tweeners[n] = ft.tweeners[n] || []),
          ft.tweeners[n].unshift(t)
    },
    prefilters: [
      function (e, t, n) {
        var r,
          i,
          o,
          a,
          s,
          u,
          l,
          c,
          f = 'width' in t || 'height' in t,
          p = this,
          d = {},
          h = e.style,
          g = e.nodeType && ae(e),
          v = Y.get(e, 'fxshow')
        for (r in (n.queue ||
          (null == (a = S._queueHooks(e, 'fx')).unqueued &&
            ((a.unqueued = 0),
            (s = a.empty.fire),
            (a.empty.fire = function () {
              a.unqueued || s()
            })),
          a.unqueued++,
          p.always(function () {
            p.always(function () {
              a.unqueued--, S.queue(e, 'fx').length || a.empty.fire()
            })
          })),
        t))
          if (((i = t[r]), ot.test(i))) {
            if (
              (delete t[r],
              (o = o || 'toggle' === i),
              i === (g ? 'hide' : 'show'))
            ) {
              if ('show' !== i || !v || void 0 === v[r]) continue
              g = !0
            }
            d[r] = (v && v[r]) || S.style(e, r)
          }
        if ((u = !S.isEmptyObject(t)) || !S.isEmptyObject(d))
          for (r in (f &&
            1 === e.nodeType &&
            ((n.overflow = [h.overflow, h.overflowX, h.overflowY]),
            null == (l = v && v.display) && (l = Y.get(e, 'display')),
            'none' === (c = S.css(e, 'display')) &&
              (l
                ? (c = l)
                : (le([e], !0),
                  (l = e.style.display || l),
                  (c = S.css(e, 'display')),
                  le([e]))),
            ('inline' === c || ('inline-block' === c && null != l)) &&
              'none' === S.css(e, 'float') &&
              (u ||
                (p.done(function () {
                  h.display = l
                }),
                null == l && ((c = h.display), (l = 'none' === c ? '' : c))),
              (h.display = 'inline-block'))),
          n.overflow &&
            ((h.overflow = 'hidden'),
            p.always(function () {
              ;(h.overflow = n.overflow[0]),
                (h.overflowX = n.overflow[1]),
                (h.overflowY = n.overflow[2])
            })),
          (u = !1),
          d))
            u ||
              (v
                ? 'hidden' in v && (g = v.hidden)
                : (v = Y.access(e, 'fxshow', { display: l })),
              o && (v.hidden = !g),
              g && le([e], !0),
              p.done(function () {
                for (r in (g || le([e]), Y.remove(e, 'fxshow'), d))
                  S.style(e, r, d[r])
              })),
              (u = ct(g ? v[r] : 0, r, p)),
              r in v ||
                ((v[r] = u.start), g && ((u.end = u.start), (u.start = 0)))
      },
    ],
    prefilter: function (e, t) {
      t ? ft.prefilters.unshift(e) : ft.prefilters.push(e)
    },
  })),
    (S.speed = function (e, t, n) {
      var r =
        e && 'object' == typeof e
          ? S.extend({}, e)
          : {
              complete: n || (!n && t) || (m(e) && e),
              duration: e,
              easing: (n && t) || (t && !m(t) && t),
            }
      return (
        S.fx.off
          ? (r.duration = 0)
          : 'number' != typeof r.duration &&
            (r.duration in S.fx.speeds
              ? (r.duration = S.fx.speeds[r.duration])
              : (r.duration = S.fx.speeds._default)),
        (null != r.queue && !0 !== r.queue) || (r.queue = 'fx'),
        (r.old = r.complete),
        (r.complete = function () {
          m(r.old) && r.old.call(this), r.queue && S.dequeue(this, r.queue)
        }),
        r
      )
    }),
    S.fn.extend({
      fadeTo: function (e, t, n, r) {
        return this.filter(ae)
          .css('opacity', 0)
          .show()
          .end()
          .animate({ opacity: t }, e, n, r)
      },
      animate: function (t, e, n, r) {
        var i = S.isEmptyObject(t),
          o = S.speed(e, n, r),
          a = function () {
            var e = ft(this, S.extend({}, t), o)
            ;(i || Y.get(this, 'finish')) && e.stop(!0)
          }
        return (
          (a.finish = a),
          i || !1 === o.queue ? this.each(a) : this.queue(o.queue, a)
        )
      },
      stop: function (i, e, o) {
        var a = function (e) {
          var t = e.stop
          delete e.stop, t(o)
        }
        return (
          'string' != typeof i && ((o = e), (e = i), (i = void 0)),
          e && this.queue(i || 'fx', []),
          this.each(function () {
            var e = !0,
              t = null != i && i + 'queueHooks',
              n = S.timers,
              r = Y.get(this)
            if (t) r[t] && r[t].stop && a(r[t])
            else for (t in r) r[t] && r[t].stop && at.test(t) && a(r[t])
            for (t = n.length; t--; )
              n[t].elem !== this ||
                (null != i && n[t].queue !== i) ||
                (n[t].anim.stop(o), (e = !1), n.splice(t, 1))
            ;(!e && o) || S.dequeue(this, i)
          })
        )
      },
      finish: function (a) {
        return (
          !1 !== a && (a = a || 'fx'),
          this.each(function () {
            var e,
              t = Y.get(this),
              n = t[a + 'queue'],
              r = t[a + 'queueHooks'],
              i = S.timers,
              o = n ? n.length : 0
            for (
              t.finish = !0,
                S.queue(this, a, []),
                r && r.stop && r.stop.call(this, !0),
                e = i.length;
              e--;

            )
              i[e].elem === this &&
                i[e].queue === a &&
                (i[e].anim.stop(!0), i.splice(e, 1))
            for (e = 0; e < o; e++)
              n[e] && n[e].finish && n[e].finish.call(this)
            delete t.finish
          })
        )
      },
    }),
    S.each(['toggle', 'show', 'hide'], function (e, r) {
      var i = S.fn[r]
      S.fn[r] = function (e, t, n) {
        return null == e || 'boolean' == typeof e
          ? i.apply(this, arguments)
          : this.animate(lt(r, !0), e, t, n)
      }
    }),
    S.each(
      {
        slideDown: lt('show'),
        slideUp: lt('hide'),
        slideToggle: lt('toggle'),
        fadeIn: { opacity: 'show' },
        fadeOut: { opacity: 'hide' },
        fadeToggle: { opacity: 'toggle' },
      },
      function (e, r) {
        S.fn[e] = function (e, t, n) {
          return this.animate(r, e, t, n)
        }
      }
    ),
    (S.timers = []),
    (S.fx.tick = function () {
      var e,
        t = 0,
        n = S.timers
      for (tt = Date.now(); t < n.length; t++)
        (e = n[t])() || n[t] !== e || n.splice(t--, 1)
      n.length || S.fx.stop(), (tt = void 0)
    }),
    (S.fx.timer = function (e) {
      S.timers.push(e), S.fx.start()
    }),
    (S.fx.interval = 13),
    (S.fx.start = function () {
      nt || ((nt = !0), st())
    }),
    (S.fx.stop = function () {
      nt = null
    }),
    (S.fx.speeds = { slow: 600, fast: 200, _default: 400 }),
    (S.fn.delay = function (r, e) {
      return (
        (r = (S.fx && S.fx.speeds[r]) || r),
        (e = e || 'fx'),
        this.queue(e, function (e, t) {
          var n = C.setTimeout(e, r)
          t.stop = function () {
            C.clearTimeout(n)
          }
        })
      )
    }),
    (rt = E.createElement('input')),
    (it = E.createElement('select').appendChild(E.createElement('option'))),
    (rt.type = 'checkbox'),
    (y.checkOn = '' !== rt.value),
    (y.optSelected = it.selected),
    ((rt = E.createElement('input')).value = 't'),
    (rt.type = 'radio'),
    (y.radioValue = 't' === rt.value)
  var pt,
    dt = S.expr.attrHandle
  S.fn.extend({
    attr: function (e, t) {
      return $(this, S.attr, e, t, 1 < arguments.length)
    },
    removeAttr: function (e) {
      return this.each(function () {
        S.removeAttr(this, e)
      })
    },
  }),
    S.extend({
      attr: function (e, t, n) {
        var r,
          i,
          o = e.nodeType
        if (3 !== o && 8 !== o && 2 !== o)
          return 'undefined' == typeof e.getAttribute
            ? S.prop(e, t, n)
            : ((1 === o && S.isXMLDoc(e)) ||
                (i =
                  S.attrHooks[t.toLowerCase()] ||
                  (S.expr.match.bool.test(t) ? pt : void 0)),
              void 0 !== n
                ? null === n
                  ? void S.removeAttr(e, t)
                  : i && 'set' in i && void 0 !== (r = i.set(e, n, t))
                  ? r
                  : (e.setAttribute(t, n + ''), n)
                : i && 'get' in i && null !== (r = i.get(e, t))
                ? r
                : null == (r = S.find.attr(e, t))
                ? void 0
                : r)
      },
      attrHooks: {
        type: {
          set: function (e, t) {
            if (!y.radioValue && 'radio' === t && A(e, 'input')) {
              var n = e.value
              return e.setAttribute('type', t), n && (e.value = n), t
            }
          },
        },
      },
      removeAttr: function (e, t) {
        var n,
          r = 0,
          i = t && t.match(P)
        if (i && 1 === e.nodeType) while ((n = i[r++])) e.removeAttribute(n)
      },
    }),
    (pt = {
      set: function (e, t, n) {
        return !1 === t ? S.removeAttr(e, n) : e.setAttribute(n, n), n
      },
    }),
    S.each(S.expr.match.bool.source.match(/\w+/g), function (e, t) {
      var a = dt[t] || S.find.attr
      dt[t] = function (e, t, n) {
        var r,
          i,
          o = t.toLowerCase()
        return (
          n ||
            ((i = dt[o]),
            (dt[o] = r),
            (r = null != a(e, t, n) ? o : null),
            (dt[o] = i)),
          r
        )
      }
    })
  var ht = /^(?:input|select|textarea|button)$/i,
    gt = /^(?:a|area)$/i
  function vt(e) {
    return (e.match(P) || []).join(' ')
  }
  function yt(e) {
    return (e.getAttribute && e.getAttribute('class')) || ''
  }
  function mt(e) {
    return Array.isArray(e) ? e : ('string' == typeof e && e.match(P)) || []
  }
  S.fn.extend({
    prop: function (e, t) {
      return $(this, S.prop, e, t, 1 < arguments.length)
    },
    removeProp: function (e) {
      return this.each(function () {
        delete this[S.propFix[e] || e]
      })
    },
  }),
    S.extend({
      prop: function (e, t, n) {
        var r,
          i,
          o = e.nodeType
        if (3 !== o && 8 !== o && 2 !== o)
          return (
            (1 === o && S.isXMLDoc(e)) ||
              ((t = S.propFix[t] || t), (i = S.propHooks[t])),
            void 0 !== n
              ? i && 'set' in i && void 0 !== (r = i.set(e, n, t))
                ? r
                : (e[t] = n)
              : i && 'get' in i && null !== (r = i.get(e, t))
              ? r
              : e[t]
          )
      },
      propHooks: {
        tabIndex: {
          get: function (e) {
            var t = S.find.attr(e, 'tabindex')
            return t
              ? parseInt(t, 10)
              : ht.test(e.nodeName) || (gt.test(e.nodeName) && e.href)
              ? 0
              : -1
          },
        },
      },
      propFix: { for: 'htmlFor', class: 'className' },
    }),
    y.optSelected ||
      (S.propHooks.selected = {
        get: function (e) {
          var t = e.parentNode
          return t && t.parentNode && t.parentNode.selectedIndex, null
        },
        set: function (e) {
          var t = e.parentNode
          t && (t.selectedIndex, t.parentNode && t.parentNode.selectedIndex)
        },
      }),
    S.each(
      [
        'tabIndex',
        'readOnly',
        'maxLength',
        'cellSpacing',
        'cellPadding',
        'rowSpan',
        'colSpan',
        'useMap',
        'frameBorder',
        'contentEditable',
      ],
      function () {
        S.propFix[this.toLowerCase()] = this
      }
    ),
    S.fn.extend({
      addClass: function (t) {
        var e,
          n,
          r,
          i,
          o,
          a,
          s,
          u = 0
        if (m(t))
          return this.each(function (e) {
            S(this).addClass(t.call(this, e, yt(this)))
          })
        if ((e = mt(t)).length)
          while ((n = this[u++]))
            if (((i = yt(n)), (r = 1 === n.nodeType && ' ' + vt(i) + ' '))) {
              a = 0
              while ((o = e[a++]))
                r.indexOf(' ' + o + ' ') < 0 && (r += o + ' ')
              i !== (s = vt(r)) && n.setAttribute('class', s)
            }
        return this
      },
      removeClass: function (t) {
        var e,
          n,
          r,
          i,
          o,
          a,
          s,
          u = 0
        if (m(t))
          return this.each(function (e) {
            S(this).removeClass(t.call(this, e, yt(this)))
          })
        if (!arguments.length) return this.attr('class', '')
        if ((e = mt(t)).length)
          while ((n = this[u++]))
            if (((i = yt(n)), (r = 1 === n.nodeType && ' ' + vt(i) + ' '))) {
              a = 0
              while ((o = e[a++]))
                while (-1 < r.indexOf(' ' + o + ' '))
                  r = r.replace(' ' + o + ' ', ' ')
              i !== (s = vt(r)) && n.setAttribute('class', s)
            }
        return this
      },
      toggleClass: function (i, t) {
        var o = typeof i,
          a = 'string' === o || Array.isArray(i)
        return 'boolean' == typeof t && a
          ? t
            ? this.addClass(i)
            : this.removeClass(i)
          : m(i)
          ? this.each(function (e) {
              S(this).toggleClass(
                i && typeof i === 'function' ? i.call(this, e, yt(this), t) : i,
                t
              )
            })
          : this.each(function () {
              var e, t, n, r
              if (a) {
                ;(t = 0), (n = S(this)), (r = mt(i))
                while ((e = r[t++]))
                  n.hasClass(e) ? n.removeClass(e) : n.addClass(e)
              } else (void 0 !== i && 'boolean' !== o) || ((e = yt(this)) && Y.set(this, '__className__', e), this.setAttribute && this.setAttribute('class', e || !1 === i ? '' : Y.get(this, '__className__') || ''))
            })
      },
      hasClass: function (e) {
        var t,
          n,
          r = 0
        t = ' ' + e + ' '
        while ((n = this[r++]))
          if (1 === n.nodeType && -1 < (' ' + vt(yt(n)) + ' ').indexOf(t))
            return !0
        return !1
      },
    })
  var xt = /\r/g
  S.fn.extend({
    val: function (n) {
      var r,
        e,
        i,
        t = this[0]
      return arguments.length
        ? ((i = m(n)),
          this.each(function (e) {
            var t
            1 === this.nodeType &&
              (null == (t = i ? n.call(this, e, S(this).val()) : n)
                ? (t = '')
                : 'number' == typeof t
                ? (t += '')
                : Array.isArray(t) &&
                  (t = S.map(t, function (e) {
                    return null == e ? '' : e + ''
                  })),
              ((r =
                S.valHooks[this.type] ||
                S.valHooks[this.nodeName.toLowerCase()]) &&
                'set' in r &&
                void 0 !== r.set(this, t, 'value')) ||
                (this.value = t))
          }))
        : t
        ? (r = S.valHooks[t.type] || S.valHooks[t.nodeName.toLowerCase()]) &&
          'get' in r &&
          void 0 !== (e = r.get(t, 'value'))
          ? e
          : 'string' == typeof (e = t.value)
          ? e.replace(xt, '')
          : null == e
          ? ''
          : e
        : void 0
    },
  }),
    S.extend({
      valHooks: {
        option: {
          get: function (e) {
            var t = S.find.attr(e, 'value')
            return null != t ? t : vt(S.text(e))
          },
        },
        select: {
          get: function (e) {
            var t,
              n,
              r,
              i = e.options,
              o = e.selectedIndex,
              a = 'select-one' === e.type,
              s = a ? null : [],
              u = a ? o + 1 : i.length
            for (r = o < 0 ? u : a ? o : 0; r < u; r++)
              if (
                ((n = i[r]).selected || r === o) &&
                !n.disabled &&
                (!n.parentNode.disabled || !A(n.parentNode, 'optgroup'))
              ) {
                if (((t = S(n).val()), a)) return t
                s.push(t)
              }
            return s
          },
          set: function (e, t) {
            var n,
              r,
              i = e.options,
              o = S.makeArray(t),
              a = i.length
            while (a--)
              ((r = i[a]).selected =
                -1 < S.inArray(S.valHooks.option.get(r), o)) && (n = !0)
            return n || (e.selectedIndex = -1), o
          },
        },
      },
    }),
    S.each(['radio', 'checkbox'], function () {
      ;(S.valHooks[this] = {
        set: function (e, t) {
          if (Array.isArray(t))
            return (e.checked = -1 < S.inArray(S(e).val(), t))
        },
      }),
        y.checkOn ||
          (S.valHooks[this].get = function (e) {
            return null === e.getAttribute('value') ? 'on' : e.value
          })
    }),
    (y.focusin = 'onfocusin' in C)
  var bt = /^(?:focusinfocus|focusoutblur)$/,
    wt = function (e) {
      e.stopPropagation()
    }
  S.extend(S.event, {
    trigger: function (e, t, n, r) {
      var i,
        o,
        a,
        s,
        u,
        l,
        c,
        f,
        p = [n || E],
        d = v.call(e, 'type') ? e.type : e,
        h = v.call(e, 'namespace') ? e.namespace.split('.') : []
      if (
        ((o = f = a = n = n || E),
        3 !== n.nodeType &&
          8 !== n.nodeType &&
          !bt.test(d + S.event.triggered) &&
          (-1 < d.indexOf('.') && ((d = (h = d.split('.')).shift()), h.sort()),
          (u = d.indexOf(':') < 0 && 'on' + d),
          ((e = e[S.expando]
            ? e
            : new S.Event(d, 'object' == typeof e && e)).isTrigger = r ? 2 : 3),
          (e.namespace = h.join('.')),
          (e.rnamespace = e.namespace
            ? new RegExp('(^|\\.)' + h.join('\\.(?:.*\\.|)') + '(\\.|$)')
            : null),
          (e.result = void 0),
          e.target || (e.target = n),
          (t = null == t ? [e] : S.makeArray(t, [e])),
          (c = S.event.special[d] || {}),
          r || !c.trigger || !1 !== c.trigger.apply(n, t)))
      ) {
        if (!r && !c.noBubble && !x(n)) {
          for (
            s = c.delegateType || d, bt.test(s + d) || (o = o.parentNode);
            o;
            o = o.parentNode
          )
            p.push(o), (a = o)
          a === (n.ownerDocument || E) &&
            p.push(a.defaultView || a.parentWindow || C)
        }
        i = 0
        while ((o = p[i++]) && !e.isPropagationStopped())
          (f = o),
            (e.type = 1 < i ? s : c.bindType || d),
            (l =
              (Y.get(o, 'events') || Object.create(null))[e.type] &&
              Y.get(o, 'handle')) && l.apply(o, t),
            (l = u && o[u]) &&
              l.apply &&
              V(o) &&
              ((e.result = l.apply(o, t)),
              !1 === e.result && e.preventDefault())
        return (
          (e.type = d),
          r ||
            e.isDefaultPrevented() ||
            (c._default && !1 !== c._default.apply(p.pop(), t)) ||
            !V(n) ||
            (u &&
              m(n[d]) &&
              !x(n) &&
              ((a = n[u]) && (n[u] = null),
              (S.event.triggered = d),
              e.isPropagationStopped() && f.addEventListener(d, wt),
              n[d](),
              e.isPropagationStopped() && f.removeEventListener(d, wt),
              (S.event.triggered = void 0),
              a && (n[u] = a))),
          e.result
        )
      }
    },
    simulate: function (e, t, n) {
      var r = S.extend(new S.Event(), n, { type: e, isSimulated: !0 })
      S.event.trigger(r, null, t)
    },
  }),
    S.fn.extend({
      trigger: function (e, t) {
        return this.each(function () {
          S.event.trigger(e, t, this)
        })
      },
      triggerHandler: function (e, t) {
        var n = this[0]
        if (n) return S.event.trigger(e, t, n, !0)
      },
    }),
    y.focusin ||
      S.each({ focus: 'focusin', blur: 'focusout' }, function (n, r) {
        var i = function (e) {
          S.event.simulate(r, e.target, S.event.fix(e))
        }
        S.event.special[r] = {
          setup: function () {
            var e = this.ownerDocument || this.document || this,
              t = Y.access(e, r)
            t || e.addEventListener(n, i, !0), Y.access(e, r, (t || 0) + 1)
          },
          teardown: function () {
            var e = this.ownerDocument || this.document || this,
              t = Y.access(e, r) - 1
            t
              ? Y.access(e, r, t)
              : (e.removeEventListener(n, i, !0), Y.remove(e, r))
          },
        }
      })
  var Tt = C.location,
    Ct = { guid: Date.now() },
    Et = /\?/
  S.parseXML = function (e) {
    var t
    if (!e || 'string' != typeof e) return null
    try {
      t = new C.DOMParser().parseFromString(e, 'text/xml')
    } catch (e) {
      t = void 0
    }
    return (
      (t && !t.getElementsByTagName('parsererror').length) ||
        S.error('Invalid XML: ' + e),
      t
    )
  }
  var St = /\[\]$/,
    kt = /\r?\n/g,
    At = /^(?:submit|button|image|reset|file)$/i,
    Nt = /^(?:input|select|textarea|keygen)/i
  function Dt(n, e, r, i) {
    var t
    if (Array.isArray(e))
      S.each(e, function (e, t) {
        r || St.test(n)
          ? i(n, t)
          : Dt(
              n + '[' + ('object' == typeof t && null != t ? e : '') + ']',
              t,
              r,
              i
            )
      })
    else if (r || 'object' !== w(e)) i(n, e)
    else for (t in e) Dt(n + '[' + t + ']', e[t], r, i)
  }
  ;(S.param = function (e, t) {
    var n,
      r = [],
      i = function (e, t) {
        var n = m(t) ? t() : t
        r[r.length] =
          encodeURIComponent(e) + '=' + encodeURIComponent(null == n ? '' : n)
      }
    if (null == e) return ''
    if (Array.isArray(e) || (e.jquery && !S.isPlainObject(e)))
      S.each(e, function () {
        i(this.name, this.value)
      })
    else for (n in e) Dt(n, e[n], t, i)
    return r.join('&')
  }),
    S.fn.extend({
      serialize: function () {
        return S.param(this.serializeArray())
      },
      serializeArray: function () {
        return this.map(function () {
          var e = S.prop(this, 'elements')
          return e ? S.makeArray(e) : this
        })
          .filter(function () {
            var e = this.type
            return (
              this.name &&
              !S(this).is(':disabled') &&
              Nt.test(this.nodeName) &&
              !At.test(e) &&
              (this.checked || !pe.test(e))
            )
          })
          .map(function (e, t) {
            var n = S(this).val()
            return null == n
              ? null
              : Array.isArray(n)
              ? S.map(n, function (e) {
                  return { name: t.name, value: e.replace(kt, '\r\n') }
                })
              : { name: t.name, value: n.replace(kt, '\r\n') }
          })
          .get()
      },
    })
  var jt = /%20/g,
    qt = /#.*$/,
    Lt = /([?&])_=[^&]*/,
    Ht = /^(.*?):[ \t]*([^\r\n]*)$/gm,
    Ot = /^(?:GET|HEAD)$/,
    Pt = /^\/\//,
    Rt = {},
    Mt = {},
    It = '*/'.concat('*'),
    Wt = E.createElement('a')
  function Ft(o) {
    return function (e, t) {
      'string' != typeof e && ((t = e), (e = '*'))
      var n,
        r = 0,
        i = e.toLowerCase().match(P) || []
      if (m(t))
        while ((n = i[r++]))
          '+' === n[0]
            ? ((n = n.slice(1) || '*'), (o[n] = o[n] || []).unshift(t))
            : (o[n] = o[n] || []).push(t)
    }
  }
  function Bt(t, i, o, a) {
    var s = {},
      u = t === Mt
    function l(e) {
      var r
      return (
        (s[e] = !0),
        S.each(t[e] || [], function (e, t) {
          var n = t(i, o, a)
          return 'string' != typeof n || u || s[n]
            ? u
              ? !(r = n)
              : void 0
            : (i.dataTypes.unshift(n), l(n), !1)
        }),
        r
      )
    }
    return l(i.dataTypes[0]) || (!s['*'] && l('*'))
  }
  function $t(e, t) {
    var n,
      r,
      i = S.ajaxSettings.flatOptions || {}
    for (n in t) void 0 !== t[n] && ((i[n] ? e : r || (r = {}))[n] = t[n])
    return r && S.extend(!0, e, r), e
  }
  ;(Wt.href = Tt.href),
    S.extend({
      active: 0,
      lastModified: {},
      etag: {},
      ajaxSettings: {
        url: Tt.href,
        type: 'GET',
        isLocal:
          /^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(
            Tt.protocol
          ),
        global: !0,
        processData: !0,
        async: !0,
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        accepts: {
          '*': It,
          text: 'text/plain',
          html: 'text/html',
          xml: 'application/xml, text/xml',
          json: 'application/json, text/javascript',
        },
        contents: { xml: /\bxml\b/, html: /\bhtml/, json: /\bjson\b/ },
        responseFields: {
          xml: 'responseXML',
          text: 'responseText',
          json: 'responseJSON',
        },
        converters: {
          '* text': String,
          'text html': !0,
          'text json': JSON.parse,
          'text xml': S.parseXML,
        },
        flatOptions: { url: !0, context: !0 },
      },
      ajaxSetup: function (e, t) {
        return t ? $t($t(e, S.ajaxSettings), t) : $t(S.ajaxSettings, e)
      },
      ajaxPrefilter: Ft(Rt),
      ajaxTransport: Ft(Mt),
      ajax: function (e, t) {
        'object' == typeof e && ((t = e), (e = void 0)), (t = t || {})
        var c,
          f,
          p,
          n,
          d,
          r,
          h,
          g,
          i,
          o,
          v = S.ajaxSetup({}, t),
          y = v.context || v,
          m = v.context && (y.nodeType || y.jquery) ? S(y) : S.event,
          x = S.Deferred(),
          b = S.Callbacks('once memory'),
          w = v.statusCode || {},
          a = {},
          s = {},
          u = 'canceled',
          T = {
            readyState: 0,
            getResponseHeader: function (e) {
              var t
              if (h) {
                if (!n) {
                  n = {}
                  while ((t = Ht.exec(p)))
                    n[t[1].toLowerCase() + ' '] = (
                      n[t[1].toLowerCase() + ' '] || []
                    ).concat(t[2])
                }
                t = n[e.toLowerCase() + ' ']
              }
              return null == t ? null : t.join(', ')
            },
            getAllResponseHeaders: function () {
              return h ? p : null
            },
            setRequestHeader: function (e, t) {
              return (
                null == h &&
                  ((e = s[e.toLowerCase()] = s[e.toLowerCase()] || e),
                  (a[e] = t)),
                this
              )
            },
            overrideMimeType: function (e) {
              return null == h && (v.mimeType = e), this
            },
            statusCode: function (e) {
              var t
              if (e)
                if (h) T.always(e[T.status])
                else for (t in e) w[t] = [w[t], e[t]]
              return this
            },
            abort: function (e) {
              var t = e || u
              return c && c.abort(t), l(0, t), this
            },
          }
        if (
          (x.promise(T),
          (v.url = ((e || v.url || Tt.href) + '').replace(
            Pt,
            Tt.protocol + '//'
          )),
          (v.type = t.method || t.type || v.method || v.type),
          (v.dataTypes = (v.dataType || '*').toLowerCase().match(P) || ['']),
          null == v.crossDomain)
        ) {
          r = E.createElement('a')
          try {
            ;(r.href = v.url),
              (r.href = r.href),
              (v.crossDomain =
                Wt.protocol + '//' + Wt.host != r.protocol + '//' + r.host)
          } catch (e) {
            v.crossDomain = !0
          }
        }
        if (
          (v.data &&
            v.processData &&
            'string' != typeof v.data &&
            (v.data = S.param(v.data, v.traditional)),
          Bt(Rt, v, t, T),
          h)
        )
          return T
        for (i in ((g = S.event && v.global) &&
          0 == S.active++ &&
          S.event.trigger('ajaxStart'),
        (v.type = v.type.toUpperCase()),
        (v.hasContent = !Ot.test(v.type)),
        (f = v.url.replace(qt, '')),
        v.hasContent
          ? v.data &&
            v.processData &&
            0 ===
              (v.contentType || '').indexOf(
                'application/x-www-form-urlencoded'
              ) &&
            (v.data = v.data.replace(jt, '+'))
          : ((o = v.url.slice(f.length)),
            v.data &&
              (v.processData || 'string' == typeof v.data) &&
              ((f += (Et.test(f) ? '&' : '?') + v.data), delete v.data),
            !1 === v.cache &&
              ((f = f.replace(Lt, '$1')),
              (o = (Et.test(f) ? '&' : '?') + '_=' + Ct.guid++ + o)),
            (v.url = f + o)),
        v.ifModified &&
          (S.lastModified[f] &&
            T.setRequestHeader('If-Modified-Since', S.lastModified[f]),
          S.etag[f] && T.setRequestHeader('If-None-Match', S.etag[f])),
        ((v.data && v.hasContent && !1 !== v.contentType) || t.contentType) &&
          T.setRequestHeader('Content-Type', v.contentType),
        T.setRequestHeader(
          'Accept',
          v.dataTypes[0] && v.accepts[v.dataTypes[0]]
            ? v.accepts[v.dataTypes[0]] +
                ('*' !== v.dataTypes[0] ? ', ' + It + '; q=0.01' : '')
            : v.accepts['*']
        ),
        v.headers))
          T.setRequestHeader(i, v.headers[i])
        if (v.beforeSend && (!1 === v.beforeSend.call(y, T, v) || h))
          return T.abort()
        if (
          ((u = 'abort'),
          b.add(v.complete),
          T.done(v.success),
          T.fail(v.error),
          (c = Bt(Mt, v, t, T)))
        ) {
          if (((T.readyState = 1), g && m.trigger('ajaxSend', [T, v]), h))
            return T
          v.async &&
            0 < v.timeout &&
            (d = C.setTimeout(function () {
              T.abort('timeout')
            }, v.timeout))
          try {
            ;(h = !1), c.send(a, l)
          } catch (e) {
            if (h) throw e
            l(-1, e)
          }
        } else l(-1, 'No Transport')
        function l(e, t, n, r) {
          var i,
            o,
            a,
            s,
            u,
            l = t
          h ||
            ((h = !0),
            d && C.clearTimeout(d),
            (c = void 0),
            (p = r || ''),
            (T.readyState = 0 < e ? 4 : 0),
            (i = (200 <= e && e < 300) || 304 === e),
            n &&
              (s = (function (e, t, n) {
                var r,
                  i,
                  o,
                  a,
                  s = e.contents,
                  u = e.dataTypes
                while ('*' === u[0])
                  u.shift(),
                    void 0 === r &&
                      (r = e.mimeType || t.getResponseHeader('Content-Type'))
                if (r)
                  for (i in s)
                    if (s[i] && s[i].test(r)) {
                      u.unshift(i)
                      break
                    }
                if (u[0] in n) o = u[0]
                else {
                  for (i in n) {
                    if (!u[0] || e.converters[i + ' ' + u[0]]) {
                      o = i
                      break
                    }
                    a || (a = i)
                  }
                  o = o || a
                }
                if (o) return o !== u[0] && u.unshift(o), n[o]
              })(v, T, n)),
            !i &&
              -1 < S.inArray('script', v.dataTypes) &&
              (v.converters['text script'] = function () {}),
            (s = (function (e, t, n, r) {
              var i,
                o,
                a,
                s,
                u,
                l = {},
                c = e.dataTypes.slice()
              if (c[1])
                for (a in e.converters) l[a.toLowerCase()] = e.converters[a]
              o = c.shift()
              while (o)
                if (
                  (e.responseFields[o] && (n[e.responseFields[o]] = t),
                  !u && r && e.dataFilter && (t = e.dataFilter(t, e.dataType)),
                  (u = o),
                  (o = c.shift()))
                )
                  if ('*' === o) o = u
                  else if ('*' !== u && u !== o) {
                    if (!(a = l[u + ' ' + o] || l['* ' + o]))
                      for (i in l)
                        if (
                          (s = i.split(' '))[1] === o &&
                          (a = l[u + ' ' + s[0]] || l['* ' + s[0]])
                        ) {
                          !0 === a
                            ? (a = l[i])
                            : !0 !== l[i] && ((o = s[0]), c.unshift(s[1]))
                          break
                        }
                    if (!0 !== a)
                      if (a && e['throws']) t = a(t)
                      else
                        try {
                          t = a(t)
                        } catch (e) {
                          return {
                            state: 'parsererror',
                            error: a
                              ? e
                              : 'No conversion from ' + u + ' to ' + o,
                          }
                        }
                  }
              return { state: 'success', data: t }
            })(v, s, T, i)),
            i
              ? (v.ifModified &&
                  ((u = T.getResponseHeader('Last-Modified')) &&
                    (S.lastModified[f] = u),
                  (u = T.getResponseHeader('etag')) && (S.etag[f] = u)),
                204 === e || 'HEAD' === v.type
                  ? (l = 'nocontent')
                  : 304 === e
                  ? (l = 'notmodified')
                  : ((l = s.state), (o = s.data), (i = !(a = s.error))))
              : ((a = l), (!e && l) || ((l = 'error'), e < 0 && (e = 0))),
            (T.status = e),
            (T.statusText = (t || l) + ''),
            i ? x.resolveWith(y, [o, l, T]) : x.rejectWith(y, [T, l, a]),
            T.statusCode(w),
            (w = void 0),
            g && m.trigger(i ? 'ajaxSuccess' : 'ajaxError', [T, v, i ? o : a]),
            b.fireWith(y, [T, l]),
            g &&
              (m.trigger('ajaxComplete', [T, v]),
              --S.active || S.event.trigger('ajaxStop')))
        }
        return T
      },
      getJSON: function (e, t, n) {
        return S.get(e, t, n, 'json')
      },
      getScript: function (e, t) {
        return S.get(e, void 0, t, 'script')
      },
    }),
    S.each(['get', 'post'], function (e, i) {
      S[i] = function (e, t, n, r) {
        return (
          m(t) && ((r = r || n), (n = t), (t = void 0)),
          S.ajax(
            S.extend(
              { url: e, type: i, dataType: r, data: t, success: n },
              S.isPlainObject(e) && e
            )
          )
        )
      }
    }),
    S.ajaxPrefilter(function (e) {
      var t
      for (t in e.headers)
        'content-type' === t.toLowerCase() &&
          (e.contentType = e.headers[t] || '')
    }),
    (S._evalUrl = function (e, t, n) {
      return S.ajax({
        url: e,
        type: 'GET',
        dataType: 'script',
        cache: !0,
        async: !1,
        global: !1,
        converters: { 'text script': function () {} },
        dataFilter: function (e) {
          S.globalEval(e, t, n)
        },
      })
    }),
    S.fn.extend({
      wrapAll: function (e) {
        var t
        return (
          this[0] &&
            (m(e) && (e = e.call(this[0])),
            (t = S(e, this[0].ownerDocument).eq(0).clone(!0)),
            this[0].parentNode && t.insertBefore(this[0]),
            t
              .map(function () {
                var e = this
                while (e.firstElementChild) e = e.firstElementChild
                return e
              })
              .append(this)),
          this
        )
      },
      wrapInner: function (n) {
        return m(n)
          ? this.each(function (e) {
              S(this).wrapInner(n.call(this, e))
            })
          : this.each(function () {
              var e = S(this),
                t = e.contents()
              t.length ? t.wrapAll(n) : e.append(n)
            })
      },
      wrap: function (t) {
        var n = m(t)
        return this.each(function (e) {
          S(this).wrapAll(n ? t.call(this, e) : t)
        })
      },
      unwrap: function (e) {
        return (
          this.parent(e)
            .not('body')
            .each(function () {
              S(this).replaceWith(this.childNodes)
            }),
          this
        )
      },
    }),
    (S.expr.pseudos.hidden = function (e) {
      return !S.expr.pseudos.visible(e)
    }),
    (S.expr.pseudos.visible = function (e) {
      return !!(e.offsetWidth || e.offsetHeight || e.getClientRects().length)
    }),
    (S.ajaxSettings.xhr = function () {
      try {
        return new C.XMLHttpRequest()
      } catch (e) {}
    })
  var _t = { 0: 200, 1223: 204 },
    zt = S.ajaxSettings.xhr()
  ;(y.cors = !!zt && 'withCredentials' in zt),
    (y.ajax = zt = !!zt),
    S.ajaxTransport(function (i) {
      var o, a
      if (y.cors || (zt && !i.crossDomain))
        return {
          send: function (e, t) {
            var n,
              r = i.xhr()
            if (
              (r.open(i.type, i.url, i.async, i.username, i.password),
              i.xhrFields)
            )
              for (n in i.xhrFields) r[n] = i.xhrFields[n]
            for (n in (i.mimeType &&
              r.overrideMimeType &&
              r.overrideMimeType(i.mimeType),
            i.crossDomain ||
              e['X-Requested-With'] ||
              (e['X-Requested-With'] = 'XMLHttpRequest'),
            e))
              r.setRequestHeader(n, e[n])
            ;(o = function (e) {
              return function () {
                o &&
                  ((o =
                    a =
                    r.onload =
                    r.onerror =
                    r.onabort =
                    r.ontimeout =
                    r.onreadystatechange =
                      null),
                  'abort' === e
                    ? r.abort()
                    : 'error' === e
                    ? 'number' != typeof r.status
                      ? t(0, 'error')
                      : t(r.status, r.statusText)
                    : t(
                        _t[r.status] || r.status,
                        r.statusText,
                        'text' !== (r.responseType || 'text') ||
                          'string' != typeof r.responseText
                          ? { binary: r.response }
                          : { text: r.responseText },
                        r.getAllResponseHeaders()
                      ))
              }
            }),
              (r.onload = o()),
              (a = r.onerror = r.ontimeout = o('error')),
              void 0 !== r.onabort
                ? (r.onabort = a)
                : (r.onreadystatechange = function () {
                    4 === r.readyState &&
                      C.setTimeout(function () {
                        o && a()
                      })
                  }),
              (o = o('abort'))
            try {
              r.send((i.hasContent && i.data) || null)
            } catch (e) {
              if (o) throw e
            }
          },
          abort: function () {
            o && o()
          },
        }
    }),
    S.ajaxPrefilter(function (e) {
      e.crossDomain && (e.contents.script = !1)
    }),
    S.ajaxSetup({
      accepts: {
        script:
          'text/javascript, application/javascript, application/ecmascript, application/x-ecmascript',
      },
      contents: { script: /\b(?:java|ecma)script\b/ },
      converters: {
        'text script': function (e) {
          return S.globalEval(e), e
        },
      },
    }),
    S.ajaxPrefilter('script', function (e) {
      void 0 === e.cache && (e.cache = !1), e.crossDomain && (e.type = 'GET')
    }),
    S.ajaxTransport('script', function (n) {
      var r, i
      if (n.crossDomain || n.scriptAttrs)
        return {
          send: function (e, t) {
            ;(r = S('<script>')
              .attr(n.scriptAttrs || {})
              .prop({ charset: n.scriptCharset, src: n.url })
              .on(
                'load error',
                (i = function (e) {
                  r.remove(),
                    (i = null),
                    e && t('error' === e.type ? 404 : 200, e.type)
                })
              )),
              E.head.appendChild(r[0])
          },
          abort: function () {
            i && i()
          },
        }
    })
  var Ut,
    Xt = [],
    Vt = /(=)\?(?=&|$)|\?\?/
  S.ajaxSetup({
    jsonp: 'callback',
    jsonpCallback: function () {
      var e = Xt.pop() || S.expando + '_' + Ct.guid++
      return (this[e] = !0), e
    },
  }),
    S.ajaxPrefilter('json jsonp', function (e, t, n) {
      var r,
        i,
        o,
        a =
          !1 !== e.jsonp &&
          (Vt.test(e.url)
            ? 'url'
            : 'string' == typeof e.data &&
              0 ===
                (e.contentType || '').indexOf(
                  'application/x-www-form-urlencoded'
                ) &&
              Vt.test(e.data) &&
              'data')
      if (a || 'jsonp' === e.dataTypes[0])
        return (
          (r = e.jsonpCallback =
            m(e.jsonpCallback) ? e.jsonpCallback() : e.jsonpCallback),
          a
            ? (e[a] = e[a].replace(Vt, '$1' + r))
            : !1 !== e.jsonp &&
              (e.url += (Et.test(e.url) ? '&' : '?') + e.jsonp + '=' + r),
          (e.converters['script json'] = function () {
            return o || S.error(r + ' was not called'), o[0]
          }),
          (e.dataTypes[0] = 'json'),
          (i = C[r]),
          (C[r] = function () {
            o = arguments
          }),
          n.always(function () {
            void 0 === i ? S(C).removeProp(r) : (C[r] = i),
              e[r] && ((e.jsonpCallback = t.jsonpCallback), Xt.push(r)),
              o && m(i) && i(o[0]),
              (o = i = void 0)
          }),
          'script'
        )
    }),
    (y.createHTMLDocument =
      (((Ut = E.implementation.createHTMLDocument('').body).innerHTML =
        '<form></form><form></form>'),
      2 === Ut.childNodes.length)),
    (S.parseHTML = function (e, t, n) {
      return 'string' != typeof e
        ? []
        : ('boolean' == typeof t && ((n = t), (t = !1)),
          t ||
            (y.createHTMLDocument
              ? (((r = (t =
                  E.implementation.createHTMLDocument('')).createElement(
                  'base'
                )).href = E.location.href),
                t.head.appendChild(r))
              : (t = E)),
          (o = !n && []),
          (i = N.exec(e))
            ? [t.createElement(i[1])]
            : ((i = xe([e], t, o)),
              o && o.length && S(o).remove(),
              S.merge([], i.childNodes)))
    }),
    (S.fn.load = function (e, t, n) {
      var r,
        i,
        o,
        a = this,
        s = e.indexOf(' ')
      return (
        -1 < s && ((r = vt(e.slice(s))), (e = e.slice(0, s))),
        m(t)
          ? ((n = t), (t = void 0))
          : t && 'object' == typeof t && (i = 'POST'),
        0 < a.length &&
          S.ajax({ url: e, type: i || 'GET', dataType: 'html', data: t })
            .done(function (e) {
              ;(o = arguments),
                a.html(r ? S('<div>').append(S.parseHTML(e)).find(r) : e)
            })
            .always(
              n &&
                function (e, t) {
                  a.each(function () {
                    n.apply(this, o || [e.responseText, t, e])
                  })
                }
            ),
        this
      )
    }),
    (S.expr.pseudos.animated = function (t) {
      return S.grep(S.timers, function (e) {
        return t === e.elem
      }).length
    }),
    (S.offset = {
      setOffset: function (e, t, n) {
        var r,
          i,
          o,
          a,
          s,
          u,
          l = S.css(e, 'position'),
          c = S(e),
          f = {}
        'static' === l && (e.style.position = 'relative'),
          (s = c.offset()),
          (o = S.css(e, 'top')),
          (u = S.css(e, 'left')),
          ('absolute' === l || 'fixed' === l) && -1 < (o + u).indexOf('auto')
            ? ((a = (r = c.position()).top), (i = r.left))
            : ((a = parseFloat(o) || 0), (i = parseFloat(u) || 0)),
          m(t) && (t = t.call(e, n, S.extend({}, s))),
          null != t.top && (f.top = t.top - s.top + a),
          null != t.left && (f.left = t.left - s.left + i),
          'using' in t
            ? t.using.call(e, f)
            : ('number' == typeof f.top && (f.top += 'px'),
              'number' == typeof f.left && (f.left += 'px'),
              c.css(f))
      },
    }),
    S.fn.extend({
      offset: function (t) {
        if (arguments.length)
          return void 0 === t
            ? this
            : this.each(function (e) {
                S.offset.setOffset(this, t, e)
              })
        var e,
          n,
          r = this[0]
        return r
          ? r.getClientRects().length
            ? ((e = r.getBoundingClientRect()),
              (n = r.ownerDocument.defaultView),
              { top: e.top + n.pageYOffset, left: e.left + n.pageXOffset })
            : { top: 0, left: 0 }
          : void 0
      },
      position: function () {
        if (this[0]) {
          var e,
            t,
            n,
            r = this[0],
            i = { top: 0, left: 0 }
          if ('fixed' === S.css(r, 'position')) t = r.getBoundingClientRect()
          else {
            ;(t = this.offset()),
              (n = r.ownerDocument),
              (e = r.offsetParent || n.documentElement)
            while (
              e &&
              (e === n.body || e === n.documentElement) &&
              'static' === S.css(e, 'position')
            )
              e = e.parentNode
            e &&
              e !== r &&
              1 === e.nodeType &&
              (((i = S(e).offset()).top += S.css(e, 'borderTopWidth', !0)),
              (i.left += S.css(e, 'borderLeftWidth', !0)))
          }
          return {
            top: t.top - i.top - S.css(r, 'marginTop', !0),
            left: t.left - i.left - S.css(r, 'marginLeft', !0),
          }
        }
      },
      offsetParent: function () {
        return this.map(function () {
          var e = this.offsetParent
          while (e && 'static' === S.css(e, 'position')) e = e.offsetParent
          return e || re
        })
      },
    }),
    S.each(
      { scrollLeft: 'pageXOffset', scrollTop: 'pageYOffset' },
      function (t, i) {
        var o = 'pageYOffset' === i
        S.fn[t] = function (e) {
          return $(
            this,
            function (e, t, n) {
              var r
              if (
                (x(e) ? (r = e) : 9 === e.nodeType && (r = e.defaultView),
                void 0 === n)
              )
                return r ? r[i] : e[t]
              r
                ? r.scrollTo(o ? r.pageXOffset : n, o ? n : r.pageYOffset)
                : (e[t] = n)
            },
            t,
            e,
            arguments.length
          )
        }
      }
    ),
    S.each(['top', 'left'], function (e, n) {
      S.cssHooks[n] = $e(y.pixelPosition, function (e, t) {
        if (t) return (t = Be(e, n)), Me.test(t) ? S(e).position()[n] + 'px' : t
      })
    }),
    S.each({ Height: 'height', Width: 'width' }, function (a, s) {
      S.each(
        { padding: 'inner' + a, content: s, '': 'outer' + a },
        function (r, o) {
          S.fn[o] = function (e, t) {
            var n = arguments.length && (r || 'boolean' != typeof e),
              i = r || (!0 === e || !0 === t ? 'margin' : 'border')
            return $(
              this,
              function (e, t, n) {
                var r
                return x(e)
                  ? 0 === o.indexOf('outer')
                    ? e['inner' + a]
                    : e.document.documentElement['client' + a]
                  : 9 === e.nodeType
                  ? ((r = e.documentElement),
                    Math.max(
                      e.body['scroll' + a],
                      r['scroll' + a],
                      e.body['offset' + a],
                      r['offset' + a],
                      r['client' + a]
                    ))
                  : void 0 === n
                  ? S.css(e, t, i)
                  : S.style(e, t, n, i)
              },
              s,
              n ? e : void 0,
              n
            )
          }
        }
      )
    }),
    S.each(
      [
        'ajaxStart',
        'ajaxStop',
        'ajaxComplete',
        'ajaxError',
        'ajaxSuccess',
        'ajaxSend',
      ],
      function (e, t) {
        S.fn[t] = function (e) {
          return this.on(t, e)
        }
      }
    ),
    S.fn.extend({
      bind: function (e, t, n) {
        return this.on(e, null, t, n)
      },
      unbind: function (e, t) {
        return this.off(e, null, t)
      },
      delegate: function (e, t, n, r) {
        return this.on(t, e, n, r)
      },
      undelegate: function (e, t, n) {
        return 1 === arguments.length
          ? this.off(e, '**')
          : this.off(t, e || '**', n)
      },
      hover: function (e, t) {
        return this.mouseenter(e).mouseleave(t || e)
      },
    }),
    S.each(
      'blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu'.split(
        ' '
      ),
      function (e, n) {
        S.fn[n] = function (e, t) {
          return 0 < arguments.length ? this.on(n, null, e, t) : this.trigger(n)
        }
      }
    )
  var Gt = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g
  ;(S.proxy = function (e, t) {
    var n, r, i
    if (('string' == typeof t && ((n = e[t]), (t = e), (e = n)), m(e)))
      return (
        (r = s.call(arguments, 2)),
        ((i = function () {
          return e.apply(t || this, r.concat(s.call(arguments)))
        }).guid = e.guid =
          e.guid || S.guid++),
        i
      )
  }),
    (S.holdReady = function (e) {
      e ? S.readyWait++ : S.ready(!0)
    }),
    (S.isArray = Array.isArray),
    (S.parseJSON = JSON.parse),
    (S.nodeName = A),
    (S.isFunction = m),
    (S.isWindow = x),
    (S.camelCase = X),
    (S.type = w),
    (S.now = Date.now),
    (S.isNumeric = function (e) {
      var t = S.type(e)
      return ('number' === t || 'string' === t) && !isNaN(e - parseFloat(e))
    }),
    (S.trim = function (e) {
      return null == e ? '' : (e + '').replace(Gt, '')
    }),
    'function' == typeof define &&
      define.amd &&
      define('jquery', [], function () {
        return S
      })
  var Yt = C.jQuery,
    Qt = C.$
  return (
    (S.noConflict = function (e) {
      return C.$ === S && (C.$ = Qt), e && C.jQuery === S && (C.jQuery = Yt), S
    }),
    'undefined' == typeof e && (C.jQuery = C.$ = S),
    S
  )
})
// DOM.event.move
//
// 2.0.0
//
// Stephen Band
//
// Triggers 'movestart', 'move' and 'moveend' events after
// mousemoves following a mousedown cross a distance threshold,
// similar to the native 'dragstart', 'drag' and 'dragend' events.
// Move events are throttled to animation frames. Move event objects
// have the properties:
//
// pageX:
// pageY:     Page coordinates of pointer.
// startX:
// startY:    Page coordinates of pointer at movestart.
// distX:
// distY:     Distance the pointer has moved since movestart.
// deltaX:
// deltaY:    Distance the finger has moved since last event.
// velocityX:
// velocityY: Average velocity over last few events.
;(function (fn) {
  if (typeof define === 'function' && define.amd) {
    define([], fn)
  } else if (
    typeof module !== 'undefined' &&
    module !== null &&
    module.exports
  ) {
    module.exports = fn
  } else {
    fn()
  }
})(function () {
  var assign = Object.assign || (window.jQuery && jQuery.extend)

  // Number of pixels a pressed pointer travels before movestart
  // event is fired.
  var threshold = 8

  // Shim for requestAnimationFrame, falling back to timer. See:
  // see http://paulirish.com/2011/requestanimationframe-for-smart-animating/
  var requestFrame = (function () {
    return (
      window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.oRequestAnimationFrame ||
      window.msRequestAnimationFrame ||
      function (fn, element) {
        return window.setTimeout(function () {
          fn()
        }, 25)
      }
    )
  })()

  // Shim for customEvent
  // see https://developer.mozilla.org/en-US/docs/Web/API/CustomEvent/CustomEvent#Polyfill
  ;(function () {
    if (typeof window.CustomEvent === 'function') return false
    function CustomEvent(event, params) {
      params = params || {
        bubbles: false,
        cancelable: false,
        detail: undefined,
      }
      var evt = document.createEvent('CustomEvent')
      evt.initCustomEvent(
        event,
        params.bubbles,
        params.cancelable,
        params.detail
      )
      return evt
    }

    CustomEvent.prototype = window.Event.prototype
    window.CustomEvent = CustomEvent
  })()

  var ignoreTags = {
    textarea: true,
    input: true,
    select: true,
    button: true,
  }

  var mouseevents = {
    move: 'mousemove',
    cancel: 'mouseup dragstart',
    end: 'mouseup',
  }

  var touchevents = {
    move: 'touchmove',
    cancel: 'touchend',
    end: 'touchend',
  }

  var rspaces = /\s+/

  // DOM Events

  var eventOptions = { bubbles: true, cancelable: true }

  var eventsSymbol = typeof Symbol === 'function' ? Symbol('events') : {}

  function createEvent(type) {
    return new CustomEvent(type, eventOptions)
  }

  function getEvents(node) {
    return node[eventsSymbol] || (node[eventsSymbol] = {})
  }

  function on(node, types, fn, data, selector) {
    types = types.split(rspaces)

    var events = getEvents(node)
    var i = types.length
    var handlers, type

    function handler(e) {
      fn(e, data)
    }

    while (i--) {
      type = types[i]
      handlers = events[type] || (events[type] = [])
      handlers.push([fn, handler])
      node.addEventListener(type, handler)
    }
  }

  function off(node, types, fn, selector) {
    types = types.split(rspaces)

    var events = getEvents(node)
    var i = types.length
    var type, handlers, k

    if (!events) {
      return
    }

    while (i--) {
      type = types[i]
      handlers = events[type]
      if (!handlers) {
        continue
      }
      k = handlers.length
      while (k--) {
        if (handlers[k][0] === fn) {
          node.removeEventListener(type, handlers[k][1])
          handlers.splice(k, 1)
        }
      }
    }
  }

  function trigger(node, type, properties) {
    // Don't cache events. It prevents you from triggering an event of a
    // given type from inside the handler of another event of that type.
    var event = createEvent(type)
    if (properties) {
      assign(event, properties)
    }
    node.dispatchEvent(event)
  }

  // Constructors

  function Timer(fn) {
    var callback = fn,
      active = false,
      running = false

    function trigger(time) {
      if (active) {
        callback()
        requestFrame(trigger)
        running = true
        active = false
      } else {
        running = false
      }
    }

    this.kick = function (fn) {
      active = true
      if (!running) {
        trigger()
      }
    }

    this.end = function (fn) {
      var cb = callback

      if (!fn) {
        return
      }

      // If the timer is not running, simply call the end callback.
      if (!running) {
        fn()
      }
      // If the timer is running, and has been kicked lately, then
      // queue up the current callback and the end callback, otherwise
      // just the end callback.
      else {
        callback = active
          ? function () {
              cb()
              fn()
            }
          : fn

        active = true
      }
    }
  }

  // Functions

  function noop() {}

  function preventDefault(e) {
    e.preventDefault()
  }

  function isIgnoreTag(e) {
    return !!ignoreTags[e.target.tagName.toLowerCase()]
  }

  function isPrimaryButton(e) {
    // Ignore mousedowns on any button other than the left (or primary)
    // mouse button, or when a modifier key is pressed.
    return e.which === 1 && !e.ctrlKey && !e.altKey
  }

  function identifiedTouch(touchList, id) {
    var i, l

    if (touchList.identifiedTouch) {
      return touchList.identifiedTouch(id)
    }

    // touchList.identifiedTouch() does not exist in
    // webkit yet… we must do the search ourselves...

    i = -1
    l = touchList.length

    while (++i < l) {
      if (touchList[i].identifier === id) {
        return touchList[i]
      }
    }
  }

  function changedTouch(e, data) {
    var touch = identifiedTouch(e.changedTouches, data.identifier)

    // This isn't the touch you're looking for.
    if (!touch) {
      return
    }

    // Chrome Android (at least) includes touches that have not
    // changed in e.changedTouches. That's a bit annoying. Check
    // that this touch has changed.
    if (touch.pageX === data.pageX && touch.pageY === data.pageY) {
      return
    }

    return touch
  }

  // Handlers that decide when the first movestart is triggered

  function mousedown(e) {
    // Ignore non-primary buttons
    if (!isPrimaryButton(e)) {
      return
    }

    // Ignore form and interactive elements
    if (isIgnoreTag(e)) {
      return
    }

    on(document, mouseevents.move, mousemove, e)
    on(document, mouseevents.cancel, mouseend, e)
  }

  function mousemove(e, data) {
    checkThreshold(e, data, e, removeMouse)
  }

  function mouseend(e, data) {
    removeMouse()
  }

  function removeMouse() {
    off(document, mouseevents.move, mousemove)
    off(document, mouseevents.cancel, mouseend)
  }

  function touchstart(e) {
    // Don't get in the way of interaction with form elements
    if (ignoreTags[e.target.tagName.toLowerCase()]) {
      return
    }

    var touch = e.changedTouches[0]

    // iOS live updates the touch objects whereas Android gives us copies.
    // That means we can't trust the touchstart object to stay the same,
    // so we must copy the data. This object acts as a template for
    // movestart, move and moveend event objects.
    var data = {
      target: touch.target,
      pageX: touch.pageX,
      pageY: touch.pageY,
      identifier: touch.identifier,

      // The only way to make handlers individually unbindable is by
      // making them unique.
      touchmove: function (e, data) {
        touchmove(e, data)
      },
      touchend: function (e, data) {
        touchend(e, data)
      },
    }

    on(document, touchevents.move, data.touchmove, data)
    on(document, touchevents.cancel, data.touchend, data)
  }

  function touchmove(e, data) {
    var touch = changedTouch(e, data)
    if (!touch) {
      return
    }
    checkThreshold(e, data, touch, removeTouch)
  }

  function touchend(e, data) {
    var touch = identifiedTouch(e.changedTouches, data.identifier)
    if (!touch) {
      return
    }
    removeTouch(data)
  }

  function removeTouch(data) {
    off(document, touchevents.move, data.touchmove)
    off(document, touchevents.cancel, data.touchend)
  }

  function checkThreshold(e, data, touch, fn) {
    var distX = touch.pageX - data.pageX
    var distY = touch.pageY - data.pageY

    // Do nothing if the threshold has not been crossed.
    if (distX * distX + distY * distY < threshold * threshold) {
      return
    }

    triggerStart(e, data, touch, distX, distY, fn)
  }

  function triggerStart(e, data, touch, distX, distY, fn) {
    var touches = e.targetTouches
    var time = e.timeStamp - data.timeStamp

    // Create a movestart object with some special properties that
    // are passed only to the movestart handlers.
    var template = {
      altKey: e.altKey,
      ctrlKey: e.ctrlKey,
      shiftKey: e.shiftKey,
      startX: data.pageX,
      startY: data.pageY,
      distX: distX,
      distY: distY,
      deltaX: distX,
      deltaY: distY,
      pageX: touch.pageX,
      pageY: touch.pageY,
      velocityX: distX / time,
      velocityY: distY / time,
      identifier: data.identifier,
      targetTouches: touches,
      finger: touches ? touches.length : 1,
      enableMove: function () {
        this.moveEnabled = true
        this.enableMove = noop
        e.preventDefault()
      },
    }

    // Trigger the movestart event.
    trigger(data.target, 'movestart', template)

    // Unbind handlers that tracked the touch or mouse up till now.
    fn(data)
  }

  // Handlers that control what happens following a movestart

  function activeMousemove(e, data) {
    var timer = data.timer

    data.touch = e
    data.timeStamp = e.timeStamp
    timer.kick()
  }

  function activeMouseend(e, data) {
    var target = data.target
    var event = data.event
    var timer = data.timer

    removeActiveMouse()

    endEvent(target, event, timer, function () {
      // Unbind the click suppressor, waiting until after mouseup
      // has been handled.
      setTimeout(function () {
        off(target, 'click', preventDefault)
      }, 0)
    })
  }

  function removeActiveMouse() {
    off(document, mouseevents.move, activeMousemove)
    off(document, mouseevents.end, activeMouseend)
  }

  function activeTouchmove(e, data) {
    var event = data.event
    var timer = data.timer
    var touch = changedTouch(e, event)

    if (!touch) {
      return
    }

    // Stop the interface from gesturing
    e.preventDefault()

    event.targetTouches = e.targetTouches
    data.touch = touch
    data.timeStamp = e.timeStamp

    timer.kick()
  }

  function activeTouchend(e, data) {
    var target = data.target
    var event = data.event
    var timer = data.timer
    var touch = identifiedTouch(e.changedTouches, event.identifier)

    // This isn't the touch you're looking for.
    if (!touch) {
      return
    }

    removeActiveTouch(data)
    endEvent(target, event, timer)
  }

  function removeActiveTouch(data) {
    off(document, touchevents.move, data.activeTouchmove)
    off(document, touchevents.end, data.activeTouchend)
  }

  // Logic for triggering move and moveend events

  function updateEvent(event, touch, timeStamp) {
    var time = timeStamp - event.timeStamp

    event.distX = touch.pageX - event.startX
    event.distY = touch.pageY - event.startY
    event.deltaX = touch.pageX - event.pageX
    event.deltaY = touch.pageY - event.pageY

    // Average the velocity of the last few events using a decay
    // curve to even out spurious jumps in values.
    event.velocityX = 0.3 * event.velocityX + (0.7 * event.deltaX) / time
    event.velocityY = 0.3 * event.velocityY + (0.7 * event.deltaY) / time
    event.pageX = touch.pageX
    event.pageY = touch.pageY
  }

  function endEvent(target, event, timer, fn) {
    timer.end(function () {
      trigger(target, 'moveend', event)
      return fn && fn()
    })
  }

  // Set up the DOM

  function movestart(e) {
    if (e.defaultPrevented) {
      return
    }
    if (!e.moveEnabled) {
      return
    }

    var event = {
      startX: e.startX,
      startY: e.startY,
      pageX: e.pageX,
      pageY: e.pageY,
      distX: e.distX,
      distY: e.distY,
      deltaX: e.deltaX,
      deltaY: e.deltaY,
      velocityX: e.velocityX,
      velocityY: e.velocityY,
      identifier: e.identifier,
      targetTouches: e.targetTouches,
      finger: e.finger,
    }

    var data = {
      target: e.target,
      event: event,
      timer: new Timer(update),
      touch: undefined,
      timeStamp: e.timeStamp,
    }

    function update(time) {
      updateEvent(event, data.touch, data.timeStamp)
      trigger(data.target, 'move', event)
    }

    if (e.identifier === undefined) {
      // We're dealing with a mouse event.
      // Stop clicks from propagating during a move
      on(e.target, 'click', preventDefault)
      on(document, mouseevents.move, activeMousemove, data)
      on(document, mouseevents.end, activeMouseend, data)
    } else {
      // In order to unbind correct handlers they have to be unique
      data.activeTouchmove = function (e, data) {
        activeTouchmove(e, data)
      }
      data.activeTouchend = function (e, data) {
        activeTouchend(e, data)
      }

      // We're dealing with a touch.
      on(document, touchevents.move, data.activeTouchmove, data)
      on(document, touchevents.end, data.activeTouchend, data)
    }
  }

  on(document, 'mousedown', mousedown)
  on(document, 'touchstart', touchstart)
  on(document, 'movestart', movestart)

  // jQuery special events
  //
  // jQuery event objects are copies of DOM event objects. They need
  // a little help copying the move properties across.

  if (!window.jQuery) {
    return
  }

  var properties =
    'startX startY pageX pageY distX distY deltaX deltaY velocityX velocityY'.split(
      ' '
    )

  function enableMove1(e) {
    e.enableMove()
  }
  function enableMove2(e) {
    e.enableMove()
  }
  function enableMove3(e) {
    e.enableMove()
  }

  function add(handleObj) {
    var handler = handleObj.handler

    handleObj.handler = function (e) {
      // Copy move properties across from originalEvent
      var i = properties.length
      var property

      while (i--) {
        property = properties[i]
        e[property] = e.originalEvent[property]
      }

      handler.apply(this, arguments)
    }
  }

  jQuery.event.special.movestart = {
    setup: function () {
      // Movestart must be enabled to allow other move events
      on(this, 'movestart', enableMove1)

      // Do listen to DOM events
      return false
    },

    teardown: function () {
      off(this, 'movestart', enableMove1)
      return false
    },

    add: add,
  }

  jQuery.event.special.move = {
    setup: function () {
      on(this, 'movestart', enableMove2)
      return false
    },

    teardown: function () {
      off(this, 'movestart', enableMove2)
      return false
    },

    add: add,
  }

  jQuery.event.special.moveend = {
    setup: function () {
      on(this, 'movestart', enableMove3)
      return false
    },

    teardown: function () {
      off(this, 'movestart', enableMove3)
      return false
    },

    add: add,
  }
})

// jQuery Mask Plugin v1.14.16
// github.com/igorescobar/jQuery-Mask-Plugin
// var $jscomp=$jscomp||{};$jscomp.scope={};$jscomp.findInternal=function(a,n,f){a instanceof String&&(a=String(a));for(var p=a.length,k=0;k<p;k++){var b=a[k];if(n.call(f,b,k,a))return{i:k,v:b}}return{i:-1,v:void 0}};$jscomp.ASSUME_ES5=!1;$jscomp.ASSUME_NO_NATIVE_MAP=!1;$jscomp.ASSUME_NO_NATIVE_SET=!1;$jscomp.SIMPLE_FROUND_POLYFILL=!1;
// $jscomp.defineProperty=$jscomp.ASSUME_ES5||"function"==typeof Object.defineProperties?Object.defineProperty:function(a,n,f){a!=Array.prototype&&a!=Object.prototype&&(a[n]=f.value)};$jscomp.getGlobal=function(a){return"undefined"!=typeof window&&window===a?a:"undefined"!=typeof global&&null!=global?global:a};$jscomp.global=$jscomp.getGlobal(this);
// $jscomp.polyfill=function(a,n,f,p){if(n){f=$jscomp.global;a=a.split(".");for(p=0;p<a.length-1;p++){var k=a[p];k in f||(f[k]={});f=f[k]}a=a[a.length-1];p=f[a];n=n(p);n!=p&&null!=n&&$jscomp.defineProperty(f,a,{configurable:!0,writable:!0,value:n})}};$jscomp.polyfill("Array.prototype.find",function(a){return a?a:function(a,f){return $jscomp.findInternal(this,a,f).v}},"es6","es3");
// (function(a,n,f){"function"===typeof define&&define.amd?define(["jquery"],a):"object"===typeof exports&&"undefined"===typeof Meteor?module.exports=a(require("jquery")):a(n||f)})(function(a){var n=function(b,d,e){var c={invalid:[],getCaret:function(){try{var a=0,r=b.get(0),h=document.selection,d=r.selectionStart;if(h&&-1===navigator.appVersion.indexOf("MSIE 10")){var e=h.createRange();e.moveStart("character",-c.val().length);a=e.text.length}else if(d||"0"===d)a=d;return a}catch(C){}},setCaret:function(a){try{if(b.is(":focus")){var c=
// b.get(0);if(c.setSelectionRange)c.setSelectionRange(a,a);else{var g=c.createTextRange();g.collapse(!0);g.moveEnd("character",a);g.moveStart("character",a);g.select()}}}catch(B){}},events:function(){b.on("keydown.mask",function(a){b.data("mask-keycode",a.keyCode||a.which);b.data("mask-previus-value",b.val());b.data("mask-previus-caret-pos",c.getCaret());c.maskDigitPosMapOld=c.maskDigitPosMap}).on(a.jMaskGlobals.useInput?"input.mask":"keyup.mask",c.behaviour).on("paste.mask drop.mask",function(){setTimeout(function(){b.keydown().keyup()},
// 100)}).on("change.mask",function(){b.data("changed",!0)}).on("blur.mask",function(){f===c.val()||b.data("changed")||b.trigger("change");b.data("changed",!1)}).on("blur.mask",function(){f=c.val()}).on("focus.mask",function(b){!0===e.selectOnFocus&&a(b.target).select()}).on("focusout.mask",function(){e.clearIfNotMatch&&!k.test(c.val())&&c.val("")})},getRegexMask:function(){for(var a=[],b,c,e,t,f=0;f<d.length;f++)(b=l.translation[d.charAt(f)])?(c=b.pattern.toString().replace(/.{1}$|^.{1}/g,""),e=b.optional,
// (b=b.recursive)?(a.push(d.charAt(f)),t={digit:d.charAt(f),pattern:c}):a.push(e||b?c+"?":c)):a.push(d.charAt(f).replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&"));a=a.join("");t&&(a=a.replace(new RegExp("("+t.digit+"(.*"+t.digit+")?)"),"($1)?").replace(new RegExp(t.digit,"g"),t.pattern));return new RegExp(a)},destroyEvents:function(){b.off("input keydown keyup paste drop blur focusout ".split(" ").join(".mask "))},val:function(a){var c=b.is("input")?"val":"text";if(0<arguments.length){if(b[c]()!==a)b[c](a);
// c=b}else c=b[c]();return c},calculateCaretPosition:function(a){var d=c.getMasked(),h=c.getCaret();if(a!==d){var e=b.data("mask-previus-caret-pos")||0;d=d.length;var g=a.length,f=a=0,l=0,k=0,m;for(m=h;m<d&&c.maskDigitPosMap[m];m++)f++;for(m=h-1;0<=m&&c.maskDigitPosMap[m];m--)a++;for(m=h-1;0<=m;m--)c.maskDigitPosMap[m]&&l++;for(m=e-1;0<=m;m--)c.maskDigitPosMapOld[m]&&k++;h>g?h=10*d:e>=h&&e!==g?c.maskDigitPosMapOld[h]||(e=h,h=h-(k-l)-a,c.maskDigitPosMap[h]&&(h=e)):h>e&&(h=h+(l-k)+f)}return h},behaviour:function(d){d=
// d||window.event;c.invalid=[];var e=b.data("mask-keycode");if(-1===a.inArray(e,l.byPassKeys)){e=c.getMasked();var h=c.getCaret(),g=b.data("mask-previus-value")||"";setTimeout(function(){c.setCaret(c.calculateCaretPosition(g))},a.jMaskGlobals.keyStrokeCompensation);c.val(e);c.setCaret(h);return c.callbacks(d)}},getMasked:function(a,b){var h=[],f=void 0===b?c.val():b+"",g=0,k=d.length,n=0,p=f.length,m=1,r="push",u=-1,w=0;b=[];if(e.reverse){r="unshift";m=-1;var x=0;g=k-1;n=p-1;var A=function(){return-1<
// g&&-1<n}}else x=k-1,A=function(){return g<k&&n<p};for(var z;A();){var y=d.charAt(g),v=f.charAt(n),q=l.translation[y];if(q)v.match(q.pattern)?(h[r](v),q.recursive&&(-1===u?u=g:g===x&&g!==u&&(g=u-m),x===u&&(g-=m)),g+=m):v===z?(w--,z=void 0):q.optional?(g+=m,n-=m):q.fallback?(h[r](q.fallback),g+=m,n-=m):c.invalid.push({p:n,v:v,e:q.pattern}),n+=m;else{if(!a)h[r](y);v===y?(b.push(n),n+=m):(z=y,b.push(n+w),w++);g+=m}}a=d.charAt(x);k!==p+1||l.translation[a]||h.push(a);h=h.join("");c.mapMaskdigitPositions(h,
// b,p);return h},mapMaskdigitPositions:function(a,b,d){a=e.reverse?a.length-d:0;c.maskDigitPosMap={};for(d=0;d<b.length;d++)c.maskDigitPosMap[b[d]+a]=1},callbacks:function(a){var g=c.val(),h=g!==f,k=[g,a,b,e],l=function(a,b,c){"function"===typeof e[a]&&b&&e[a].apply(this,c)};l("onChange",!0===h,k);l("onKeyPress",!0===h,k);l("onComplete",g.length===d.length,k);l("onInvalid",0<c.invalid.length,[g,a,b,c.invalid,e])}};b=a(b);var l=this,f=c.val(),k;d="function"===typeof d?d(c.val(),void 0,b,e):d;l.mask=
// d;l.options=e;l.remove=function(){var a=c.getCaret();l.options.placeholder&&b.removeAttr("placeholder");b.data("mask-maxlength")&&b.removeAttr("maxlength");c.destroyEvents();c.val(l.getCleanVal());c.setCaret(a);return b};l.getCleanVal=function(){return c.getMasked(!0)};l.getMaskedVal=function(a){return c.getMasked(!1,a)};l.init=function(g){g=g||!1;e=e||{};l.clearIfNotMatch=a.jMaskGlobals.clearIfNotMatch;l.byPassKeys=a.jMaskGlobals.byPassKeys;l.translation=a.extend({},a.jMaskGlobals.translation,e.translation);
// l=a.extend(!0,{},l,e);k=c.getRegexMask();if(g)c.events(),c.val(c.getMasked());else{e.placeholder&&b.attr("placeholder",e.placeholder);b.data("mask")&&b.attr("autocomplete","off");g=0;for(var f=!0;g<d.length;g++){var h=l.translation[d.charAt(g)];if(h&&h.recursive){f=!1;break}}f&&b.attr("maxlength",d.length).data("mask-maxlength",!0);c.destroyEvents();c.events();g=c.getCaret();c.val(c.getMasked());c.setCaret(g)}};l.init(!b.is("input"))};a.maskWatchers={};var f=function(){var b=a(this),d={},e=b.attr("data-mask");
// b.attr("data-mask-reverse")&&(d.reverse=!0);b.attr("data-mask-clearifnotmatch")&&(d.clearIfNotMatch=!0);"true"===b.attr("data-mask-selectonfocus")&&(d.selectOnFocus=!0);if(p(b,e,d))return b.data("mask",new n(this,e,d))},p=function(b,d,e){e=e||{};var c=a(b).data("mask"),f=JSON.stringify;b=a(b).val()||a(b).text();try{return"function"===typeof d&&(d=d(b)),"object"!==typeof c||f(c.options)!==f(e)||c.mask!==d}catch(w){}},k=function(a){var b=document.createElement("div");a="on"+a;var e=a in b;e||(b.setAttribute(a,
// "return;"),e="function"===typeof b[a]);return e};a.fn.mask=function(b,d){d=d||{};var e=this.selector,c=a.jMaskGlobals,f=c.watchInterval;c=d.watchInputs||c.watchInputs;var k=function(){if(p(this,b,d))return a(this).data("mask",new n(this,b,d))};a(this).each(k);e&&""!==e&&c&&(clearInterval(a.maskWatchers[e]),a.maskWatchers[e]=setInterval(function(){a(document).find(e).each(k)},f));return this};a.fn.masked=function(a){return this.data("mask").getMaskedVal(a)};a.fn.unmask=function(){clearInterval(a.maskWatchers[this.selector]);
// delete a.maskWatchers[this.selector];return this.each(function(){var b=a(this).data("mask");b&&b.remove().removeData("mask")})};a.fn.cleanVal=function(){return this.data("mask").getCleanVal()};a.applyDataMask=function(b){b=b||a.jMaskGlobals.maskElements;(b instanceof a?b:a(b)).filter(a.jMaskGlobals.dataMaskAttr).each(f)};k={maskElements:"input,td,span,div",dataMaskAttr:"*[data-mask]",dataMask:!0,watchInterval:300,watchInputs:!0,keyStrokeCompensation:10,useInput:!/Chrome\/[2-4][0-9]|SamsungBrowser/.test(window.navigator.userAgent)&&
// k("input"),watchDataMask:!1,byPassKeys:[9,16,17,18,36,37,38,39,40,91],translation:{0:{pattern:/\d/},9:{pattern:/\d/,optional:!0},"#":{pattern:/\d/,recursive:!0},A:{pattern:/[a-zA-Z0-9]/},S:{pattern:/[a-zA-Z]/}}};a.jMaskGlobals=a.jMaskGlobals||{};k=a.jMaskGlobals=a.extend(!0,{},k,a.jMaskGlobals);k.dataMask&&a.applyDataMask();setInterval(function(){a.jMaskGlobals.watchDataMask&&a.applyDataMask()},k.watchInterval)},window.jQuery,window.Zepto);

/*  jQuery Nice Select - v1.1.0
    https://github.com/hernansartorio/jquery-nice-select
    Made by Hernán Sartorio  */
;(function ($) {
  $.fn.niceSelect = function (method) {
    // Methods
    if (typeof method == 'string') {
      if (method == 'update') {
        this.each(function () {
          var $select = $(this)
          var $dropdown = $(this).next('.nice-select')
          var open = $dropdown.hasClass('open')

          if ($dropdown.length) {
            $dropdown.remove()
            create_nice_select($select)

            if (open) {
              $select.next().trigger('click')
            }
          }
        })
      } else if (method == 'destroy') {
        this.each(function () {
          var $select = $(this)
          var $dropdown = $(this).next('.nice-select')

          if ($dropdown.length) {
            $dropdown.remove()
            $select.css('display', '')
          }
        })
        if ($('.nice-select').length == 0) {
          $(document).off('.nice_select')
        }
      } else {
        //         console.log('Method "' + method + '" does not exist.')
      }
      return this
    }

    // Hide native select
    this.hide()

    // Create custom markup
    this.each(function () {
      var $select = $(this)

      if (!$select.next().hasClass('nice-select')) {
        create_nice_select.call(this, $select)
      }
    })

    function create_nice_select($select) {
      $select.after(
        $('<div></div>')
          .addClass('nice-select')
          .addClass($select.attr('class') || '')
          .addClass($select.attr('disabled') ? 'disabled' : '')
          .attr('tabindex', $select.attr('disabled') ? null : '0')
          .html('<span class="current"></span><ul class="list"></ul>')
      )

      var $dropdown = $select.next()
      var $options = $select.find('option')
      var $selected = $select.find('option:selected')

      $dropdown
        .find('.current')
        .html($selected.data('display') || $selected.text())

      $options.each(function (i) {
        var $option = $(this)
        var display = $option.data('display')

        $dropdown.find('ul').append(
          $('<li></li>')
            .attr('data-value', $option.val())
            .attr('data-display', display || null)
            .addClass(
              'option' +
                ($option.is(':selected') ? ' selected' : '') +
                ($option.is(':disabled') ? ' disabled' : '')
            )
            .html($option.text())
        )
      })
    }

    /* Event listeners */

    // Unbind existing events in case that the plugin has been initialized before
    $(document).off('.nice_select')

    // Open/close
    $(document).on('click.nice_select', '.nice-select', function (event) {
      var $dropdown = $(this)

      $('.nice-select').not($dropdown).removeClass('open')
      $dropdown.toggleClass('open')

      if ($dropdown.hasClass('open')) {
        $dropdown.find('.option')
        $dropdown.find('.focus').removeClass('focus')
        $dropdown.find('.selected').addClass('focus')
      } else {
        $dropdown.focus()
      }
    })

    // Close when clicking outside
    $(document).on('click.nice_select', function (event) {
      if ($(event.target).closest('.nice-select').length === 0) {
        $('.nice-select').removeClass('open').find('.option')
      }
    })

    // Option click
    $(document).on(
      'click.nice_select',
      '.nice-select .option:not(.disabled)',
      function (event) {
        var $option = $(this)
        var $dropdown = $option.closest('.nice-select')

        $dropdown.find('.selected').removeClass('selected')
        $option.addClass('selected')

        var text = $option.data('display') || $option.text()
        $dropdown.find('.current').text(text)

        $dropdown.prev('select').val($option.data('value')).trigger('change')
      }
    )

    // Keyboard events
    $(document).on('keydown.nice_select', '.nice-select', function (event) {
      var $dropdown = $(this)
      var $focused_option = $(
        $dropdown.find('.focus') || $dropdown.find('.list .option.selected')
      )

      // Space or Enter
      if (event.keyCode == 32 || event.keyCode == 13) {
        if ($dropdown.hasClass('open')) {
          $focused_option.trigger('click')
        } else {
          $dropdown.trigger('click')
        }
        return false
        // Down
      } else if (event.keyCode == 40) {
        if (!$dropdown.hasClass('open')) {
          $dropdown.trigger('click')
        } else {
          var $next = $focused_option.nextAll('.option:not(.disabled)').first()
          if ($next.length > 0) {
            $dropdown.find('.focus').removeClass('focus')
            $next.addClass('focus')
          }
        }
        return false
        // Up
      } else if (event.keyCode == 38) {
        if (!$dropdown.hasClass('open')) {
          $dropdown.trigger('click')
        } else {
          var $prev = $focused_option.prevAll('.option:not(.disabled)').first()
          if ($prev.length > 0) {
            $dropdown.find('.focus').removeClass('focus')
            $prev.addClass('focus')
          }
        }
        return false
        // Esc
      } else if (event.keyCode == 27) {
        if ($dropdown.hasClass('open')) {
          $dropdown.trigger('click')
        }
        // Tab
      } else if (event.keyCode == 9) {
        if ($dropdown.hasClass('open')) {
          return false
        }
      }
    })

    // Detect CSS pointer-events support, for IE <= 10. From Modernizr.
    var style = document.createElement('a').style
    style.cssText = 'pointer-events:auto'
    if (style.pointerEvents !== 'auto') {
      $('html').addClass('no-csspointerevents')
    }

    return this
  }
})(jQuery)
;(function ($) {
  $.fn.twentytwenty = function (options) {
    var options = $.extend(
      {
        default_offset_pct: 0.5,
        orientation: 'horizontal',
        before_label: 'Before',
        after_label: 'After',
        no_overlay: false,
        move_slider_on_hover: false,
        move_with_handle_only: true,
        click_to_move: false,
      },
      options
    )

    return this.each(function () {
      var sliderPct = options.default_offset_pct
      var container = $(this)
      var sliderOrientation = options.orientation
      var beforeDirection = sliderOrientation === 'vertical' ? 'down' : 'left'
      var afterDirection = sliderOrientation === 'vertical' ? 'up' : 'right'

      container.wrap(
        "<div class='twentytwenty-wrapper twentytwenty-" +
          sliderOrientation +
          "'></div>"
      )
      if (!options.no_overlay) {
        container.append("<div class='twentytwenty-overlay'></div>")
        var overlay = container.find('.twentytwenty-overlay')
        overlay.append(
          "<div class='twentytwenty-before-label' data-content='" +
            options.before_label +
            "'></div>"
        )
        overlay.append(
          "<div class='twentytwenty-after-label' data-content='" +
            options.after_label +
            "'></div>"
        )
      }
      var beforeImg = container.find('img:first')
      var afterImg = container.find('img:last')
      container.append("<div class='twentytwenty-handle'></div>")
      var slider = container.find('.twentytwenty-handle')
      slider.append(
        "<span class='twentytwenty-" + beforeDirection + "-arrow'></span>"
      )
      slider.append(
        "<span class='twentytwenty-" + afterDirection + "-arrow'></span>"
      )
      container.addClass('twentytwenty-container')
      beforeImg.addClass('twentytwenty-before')
      afterImg.addClass('twentytwenty-after')

      var calcOffset = function (dimensionPct) {
        var w = beforeImg.width()
        var h = beforeImg.height()
        return {
          w: w + 'px',
          h: h + 'px',
          cw: dimensionPct * w + 'px',
          ch: dimensionPct * h + 'px',
        }
      }

      var adjustContainer = function (offset) {
        if (sliderOrientation === 'vertical') {
          beforeImg.css('clip', 'rect(0,' + offset.w + ',' + offset.ch + ',0)')
          afterImg.css(
            'clip',
            'rect(' + offset.ch + ',' + offset.w + ',' + offset.h + ',0)'
          )
        } else {
          beforeImg.css('clip', 'rect(0,' + offset.cw + ',' + offset.h + ',0)')
          afterImg.css(
            'clip',
            'rect(0,' + offset.w + ',' + offset.h + ',' + offset.cw + ')'
          )
        }
        container.css('height', offset.h)
      }

      var adjustSlider = function (pct) {
        var offset = calcOffset(pct)
        slider.css(
          sliderOrientation === 'vertical' ? 'top' : 'left',
          sliderOrientation === 'vertical' ? offset.ch : offset.cw
        )
        adjustContainer(offset)
      }

      // Return the number specified or the min/max number if it outside the range given.
      var minMaxNumber = function (num, min, max) {
        return Math.max(min, Math.min(max, num))
      }

      // Calculate the slider percentage based on the position.
      var getSliderPercentage = function (positionX, positionY) {
        var sliderPercentage =
          sliderOrientation === 'vertical'
            ? (positionY - offsetY) / imgHeight
            : (positionX - offsetX) / imgWidth

        return minMaxNumber(sliderPercentage, 0, 1)
      }

      $(window).on('resize.twentytwenty', function (e) {
        adjustSlider(sliderPct)
      })

      var offsetX = 0
      var offsetY = 0
      var imgWidth = 0
      var imgHeight = 0
      var onMoveStart = function (e) {
        if (
          ((e.distX > e.distY && e.distX < -e.distY) ||
            (e.distX < e.distY && e.distX > -e.distY)) &&
          sliderOrientation !== 'vertical'
        ) {
          e.preventDefault()
        } else if (
          ((e.distX < e.distY && e.distX < -e.distY) ||
            (e.distX > e.distY && e.distX > -e.distY)) &&
          sliderOrientation === 'vertical'
        ) {
          e.preventDefault()
        }
        container.addClass('active')
        offsetX = container.offset().left
        offsetY = container.offset().top
        imgWidth = beforeImg.width()
        imgHeight = beforeImg.height()
      }
      var onMove = function (e) {
        if (container.hasClass('active')) {
          sliderPct = getSliderPercentage(e.pageX, e.pageY)
          adjustSlider(sliderPct)
        }
      }
      var onMoveEnd = function () {
        container.removeClass('active')
      }

      var moveTarget = options.move_with_handle_only ? slider : container
      moveTarget.on('movestart', onMoveStart)
      moveTarget.on('move', onMove)
      moveTarget.on('moveend', onMoveEnd)

      if (options.move_slider_on_hover) {
        container.on('mouseenter', onMoveStart)
        container.on('mousemove', onMove)
        container.on('mouseleave', onMoveEnd)
      }

      slider.on('touchmove', function (e) {
        e.preventDefault()
      })

      container.find('img').on('mousedown', function (event) {
        event.preventDefault()
      })

      if (options.click_to_move) {
        container.on('click', function (e) {
          offsetX = container.offset().left
          offsetY = container.offset().top
          imgWidth = beforeImg.width()
          imgHeight = beforeImg.height()

          sliderPct = getSliderPercentage(e.pageX, e.pageY)
          adjustSlider(sliderPct)
        })
      }

      $(window).trigger('resize.twentytwenty')
    })
  }
})(jQuery)

/*! jQuery Validation Plugin - v1.19.1 - 6/15/2019
 * https://jqueryvalidation.org/
 * Copyright (c) 2019 Jörn Zaefferer; Licensed MIT */
!(function (a) {
  'function' == typeof define && define.amd
    ? define(['jquery'], a)
    : 'object' == typeof module && module.exports
    ? (module.exports = a(require('jquery')))
    : a(jQuery)
})(function (a) {
  a.extend(a.fn, {
    validate: function (b) {
      if (!this.length)
        return void (
          b &&
          b.debug &&
          window.console &&
          console.warn("Nothing selected, can't validate, returning nothing.")
        )
      var c = a.data(this[0], 'validator')
      return c
        ? c
        : (this.attr('novalidate', 'novalidate'),
          (c = new a.validator(b, this[0])),
          a.data(this[0], 'validator', c),
          c.settings.onsubmit &&
            (this.on('click.validate', ':submit', function (b) {
              ;(c.submitButton = b.currentTarget),
                a(this).hasClass('cancel') && (c.cancelSubmit = !0),
                void 0 !== a(this).attr('formnovalidate') &&
                  (c.cancelSubmit = !0)
            }),
            this.on('submit.validate', function (b) {
              function d() {
                var d, e
                return (
                  c.submitButton &&
                    (c.settings.submitHandler || c.formSubmitted) &&
                    (d = a("<input type='hidden'/>")
                      .attr('name', c.submitButton.name)
                      .val(a(c.submitButton).val())
                      .appendTo(c.currentForm)),
                  !(c.settings.submitHandler && !c.settings.debug) ||
                    ((e = c.settings.submitHandler.call(c, c.currentForm, b)),
                    d && d.remove(),
                    void 0 !== e && e)
                )
              }
              return (
                c.settings.debug && b.preventDefault(),
                c.cancelSubmit
                  ? ((c.cancelSubmit = !1), d())
                  : c.form()
                  ? c.pendingRequest
                    ? ((c.formSubmitted = !0), !1)
                    : d()
                  : (c.focusInvalid(), !1)
              )
            })),
          c)
    },
    valid: function () {
      var b, c, d
      return (
        a(this[0]).is('form')
          ? (b = this.validate().form())
          : ((d = []),
            (b = !0),
            (c = a(this[0].form).validate()),
            this.each(function () {
              ;(b = c.element(this) && b), b || (d = d.concat(c.errorList))
            }),
            (c.errorList = d)),
        b
      )
    },
    rules: function (b, c) {
      var d,
        e,
        f,
        g,
        h,
        i,
        j = this[0],
        k =
          'undefined' != typeof this.attr('contenteditable') &&
          'false' !== this.attr('contenteditable')
      if (
        null != j &&
        (!j.form &&
          k &&
          ((j.form = this.closest('form')[0]), (j.name = this.attr('name'))),
        null != j.form)
      ) {
        if (b)
          switch (
            ((d = a.data(j.form, 'validator').settings),
            (e = d.rules),
            (f = a.validator.staticRules(j)),
            b)
          ) {
            case 'add':
              a.extend(f, a.validator.normalizeRule(c)),
                delete f.messages,
                (e[j.name] = f),
                c.messages &&
                  (d.messages[j.name] = a.extend(
                    d.messages[j.name],
                    c.messages
                  ))
              break
            case 'remove':
              return c
                ? ((i = {}),
                  a.each(c.split(/\s/), function (a, b) {
                    ;(i[b] = f[b]), delete f[b]
                  }),
                  i)
                : (delete e[j.name], f)
          }
        return (
          (g = a.validator.normalizeRules(
            a.extend(
              {},
              a.validator.classRules(j),
              a.validator.attributeRules(j),
              a.validator.dataRules(j),
              a.validator.staticRules(j)
            ),
            j
          )),
          g.required &&
            ((h = g.required),
            delete g.required,
            (g = a.extend({ required: h }, g))),
          g.remote &&
            ((h = g.remote), delete g.remote, (g = a.extend(g, { remote: h }))),
          g
        )
      }
    },
  }),
    a.extend(a.expr.pseudos || a.expr[':'], {
      blank: function (b) {
        return !a.trim('' + a(b).val())
      },
      filled: function (b) {
        var c = a(b).val()
        return null !== c && !!a.trim('' + c)
      },
      unchecked: function (b) {
        return !a(b).prop('checked')
      },
    }),
    (a.validator = function (b, c) {
      ;(this.settings = a.extend(!0, {}, a.validator.defaults, b)),
        (this.currentForm = c),
        this.init()
    }),
    (a.validator.format = function (b, c) {
      return 1 === arguments.length
        ? function () {
            var c = a.makeArray(arguments)
            return c.unshift(b), a.validator.format.apply(this, c)
          }
        : void 0 === c
        ? b
        : (arguments.length > 2 &&
            c.constructor !== Array &&
            (c = a.makeArray(arguments).slice(1)),
          c.constructor !== Array && (c = [c]),
          a.each(c, function (a, c) {
            b = b.replace(new RegExp('\\{' + a + '\\}', 'g'), function () {
              return c
            })
          }),
          b)
    }),
    a.extend(a.validator, {
      defaults: {
        messages: {},
        groups: {},
        rules: {},
        errorClass: 'error',
        pendingClass: 'pending',
        validClass: 'valid',
        errorElement: 'label',
        focusCleanup: !1,
        focusInvalid: !0,
        errorContainer: a([]),
        errorLabelContainer: a([]),
        onsubmit: !0,
        ignore: ':hidden',
        ignoreTitle: !1,
        onfocusin: function (a) {
          ;(this.lastActive = a),
            this.settings.focusCleanup &&
              (this.settings.unhighlight &&
                this.settings.unhighlight.call(
                  this,
                  a,
                  this.settings.errorClass,
                  this.settings.validClass
                ),
              this.hideThese(this.errorsFor(a)))
        },
        onfocusout: function (a) {
          this.checkable(a) ||
            (!(a.name in this.submitted) && this.optional(a)) ||
            this.element(a)
        },
        onkeyup: function (b, c) {
          var d = [16, 17, 18, 20, 35, 36, 37, 38, 39, 40, 45, 144, 225]
          ;(9 === c.which && '' === this.elementValue(b)) ||
            a.inArray(c.keyCode, d) !== -1 ||
            ((b.name in this.submitted || b.name in this.invalid) &&
              this.element(b))
        },
        onclick: function (a) {
          a.name in this.submitted
            ? this.element(a)
            : a.parentNode.name in this.submitted && this.element(a.parentNode)
        },
        highlight: function (b, c, d) {
          'radio' === b.type
            ? this.findByName(b.name).addClass(c).removeClass(d)
            : a(b).addClass(c).removeClass(d)
        },
        unhighlight: function (b, c, d) {
          'radio' === b.type
            ? this.findByName(b.name).removeClass(c).addClass(d)
            : a(b).removeClass(c).addClass(d)
        },
      },
      setDefaults: function (b) {
        a.extend(a.validator.defaults, b)
      },
      messages: {
        required: 'This field is required.',
        remote: 'Please fix this field.',
        email: 'Please enter a valid email address.',
        url: 'Please enter a valid URL.',
        date: 'Please enter a valid date.',
        dateISO: 'Please enter a valid date (ISO).',
        number: 'Please enter a valid number.',
        digits: 'Please enter only digits.',
        equalTo: 'Please enter the same value again.',
        maxlength: a.validator.format(
          'Please enter no more than {0} characters.'
        ),
        minlength: a.validator.format('Please enter at least {0} characters.'),
        rangelength: a.validator.format(
          'Please enter a value between {0} and {1} characters long.'
        ),
        range: a.validator.format('Please enter a value between {0} and {1}.'),
        max: a.validator.format(
          'Please enter a value less than or equal to {0}.'
        ),
        min: a.validator.format(
          'Please enter a value greater than or equal to {0}.'
        ),
        step: a.validator.format('Please enter a multiple of {0}.'),
      },
      autoCreateRanges: !1,
      prototype: {
        init: function () {
          function b(b) {
            var c =
              'undefined' != typeof a(this).attr('contenteditable') &&
              'false' !== a(this).attr('contenteditable')
            if (
              (!this.form &&
                c &&
                ((this.form = a(this).closest('form')[0]),
                (this.name = a(this).attr('name'))),
              d === this.form)
            ) {
              var e = a.data(this.form, 'validator'),
                f = 'on' + b.type.replace(/^validate/, ''),
                g = e.settings
              g[f] && !a(this).is(g.ignore) && g[f].call(e, this, b)
            }
          }
          ;(this.labelContainer = a(this.settings.errorLabelContainer)),
            (this.errorContext =
              (this.labelContainer.length && this.labelContainer) ||
              a(this.currentForm)),
            (this.containers = a(this.settings.errorContainer).add(
              this.settings.errorLabelContainer
            )),
            (this.submitted = {}),
            (this.valueCache = {}),
            (this.pendingRequest = 0),
            (this.pending = {}),
            (this.invalid = {}),
            this.reset()
          var c,
            d = this.currentForm,
            e = (this.groups = {})
          a.each(this.settings.groups, function (b, c) {
            'string' == typeof c && (c = c.split(/\s/)),
              a.each(c, function (a, c) {
                e[c] = b
              })
          }),
            (c = this.settings.rules),
            a.each(c, function (b, d) {
              c[b] = a.validator.normalizeRule(d)
            }),
            a(this.currentForm)
              .on(
                'focusin.validate focusout.validate keyup.validate',
                ":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'], [type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'], [type='radio'], [type='checkbox'], [contenteditable], [type='button']",
                b
              )
              .on(
                'click.validate',
                "select, option, [type='radio'], [type='checkbox']",
                b
              ),
            this.settings.invalidHandler &&
              a(this.currentForm).on(
                'invalid-form.validate',
                this.settings.invalidHandler
              )
        },
        form: function () {
          return (
            this.checkForm(),
            a.extend(this.submitted, this.errorMap),
            (this.invalid = a.extend({}, this.errorMap)),
            this.valid() ||
              a(this.currentForm).triggerHandler('invalid-form', [this]),
            this.showErrors(),
            this.valid()
          )
        },
        checkForm: function () {
          this.prepareForm()
          for (
            var a = 0, b = (this.currentElements = this.elements());
            b[a];
            a++
          )
            this.check(b[a])
          return this.valid()
        },
        element: function (b) {
          var c,
            d,
            e = this.clean(b),
            f = this.validationTargetFor(e),
            g = this,
            h = !0
          return (
            void 0 === f
              ? delete this.invalid[e.name]
              : (this.prepareElement(f),
                (this.currentElements = a(f)),
                (d = this.groups[f.name]),
                d &&
                  a.each(this.groups, function (a, b) {
                    b === d &&
                      a !== f.name &&
                      ((e = g.validationTargetFor(g.clean(g.findByName(a)))),
                      e &&
                        e.name in g.invalid &&
                        (g.currentElements.push(e), (h = g.check(e) && h)))
                  }),
                (c = this.check(f) !== !1),
                (h = h && c),
                c ? (this.invalid[f.name] = !1) : (this.invalid[f.name] = !0),
                this.numberOfInvalids() ||
                  (this.toHide = this.toHide.add(this.containers)),
                this.showErrors(),
                a(b).attr('aria-invalid', !c)),
            h
          )
        },
        showErrors: function (b) {
          if (b) {
            var c = this
            a.extend(this.errorMap, b),
              (this.errorList = a.map(this.errorMap, function (a, b) {
                return { message: a, element: c.findByName(b)[0] }
              })),
              (this.successList = a.grep(this.successList, function (a) {
                return !(a.name in b)
              }))
          }
          this.settings.showErrors
            ? this.settings.showErrors.call(this, this.errorMap, this.errorList)
            : this.defaultShowErrors()
        },
        resetForm: function () {
          a.fn.resetForm && a(this.currentForm).resetForm(),
            (this.invalid = {}),
            (this.submitted = {}),
            this.prepareForm(),
            this.hideErrors()
          var b = this.elements()
            .removeData('previousValue')
            .removeAttr('aria-invalid')
          this.resetElements(b)
        },
        resetElements: function (a) {
          var b
          if (this.settings.unhighlight)
            for (b = 0; a[b]; b++)
              this.settings.unhighlight.call(
                this,
                a[b],
                this.settings.errorClass,
                ''
              ),
                this.findByName(a[b].name).removeClass(this.settings.validClass)
          else
            a.removeClass(this.settings.errorClass).removeClass(
              this.settings.validClass
            )
        },
        numberOfInvalids: function () {
          return this.objectLength(this.invalid)
        },
        objectLength: function (a) {
          var b,
            c = 0
          for (b in a) void 0 !== a[b] && null !== a[b] && a[b] !== !1 && c++
          return c
        },
        hideErrors: function () {
          this.hideThese(this.toHide)
        },
        hideThese: function (a) {
          a.not(this.containers).text(''), this.addWrapper(a).hide()
        },
        valid: function () {
          return 0 === this.size()
        },
        size: function () {
          return this.errorList.length
        },
        focusInvalid: function () {
          if (this.settings.focusInvalid)
            try {
              a(
                this.findLastActive() ||
                  (this.errorList.length && this.errorList[0].element) ||
                  []
              )
                .filter(':visible')
                .trigger('focus')
                .trigger('focusin')
            } catch (b) {}
        },
        findLastActive: function () {
          var b = this.lastActive
          return (
            b &&
            1 ===
              a.grep(this.errorList, function (a) {
                return a.element.name === b.name
              }).length &&
            b
          )
        },
        elements: function () {
          var b = this,
            c = {}
          return a(this.currentForm)
            .find('input, select, textarea, [contenteditable]')
            .not(':submit, :reset, :image, :disabled')
            .not(this.settings.ignore)
            .filter(function () {
              var d = this.name || a(this).attr('name'),
                e =
                  'undefined' != typeof a(this).attr('contenteditable') &&
                  'false' !== a(this).attr('contenteditable')
              return (
                !d &&
                  b.settings.debug &&
                  window.console &&
                  console.error('%o has no name assigned', this),
                e &&
                  ((this.form = a(this).closest('form')[0]), (this.name = d)),
                this.form === b.currentForm &&
                  !(d in c || !b.objectLength(a(this).rules())) &&
                  ((c[d] = !0), !0)
              )
            })
        },
        clean: function (b) {
          return a(b)[0]
        },
        errors: function () {
          var b = this.settings.errorClass.split(' ').join('.')
          return a(this.settings.errorElement + '.' + b, this.errorContext)
        },
        resetInternals: function () {
          ;(this.successList = []),
            (this.errorList = []),
            (this.errorMap = {}),
            (this.toShow = a([])),
            (this.toHide = a([]))
        },
        reset: function () {
          this.resetInternals(), (this.currentElements = a([]))
        },
        prepareForm: function () {
          this.reset(), (this.toHide = this.errors().add(this.containers))
        },
        prepareElement: function (a) {
          this.reset(), (this.toHide = this.errorsFor(a))
        },
        elementValue: function (b) {
          var c,
            d,
            e = a(b),
            f = b.type,
            g =
              'undefined' != typeof e.attr('contenteditable') &&
              'false' !== e.attr('contenteditable')
          return 'radio' === f || 'checkbox' === f
            ? this.findByName(b.name).filter(':checked').val()
            : 'number' === f && 'undefined' != typeof b.validity
            ? b.validity.badInput
              ? 'NaN'
              : e.val()
            : ((c = g ? e.text() : e.val()),
              'file' === f
                ? 'C:\\fakepath\\' === c.substr(0, 12)
                  ? c.substr(12)
                  : ((d = c.lastIndexOf('/')),
                    d >= 0
                      ? c.substr(d + 1)
                      : ((d = c.lastIndexOf('\\')),
                        d >= 0 ? c.substr(d + 1) : c))
                : 'string' == typeof c
                ? c.replace(/\r/g, '')
                : c)
        },
        check: function (b) {
          b = this.validationTargetFor(this.clean(b))
          var c,
            d,
            e,
            f,
            g = a(b).rules(),
            h = a.map(g, function (a, b) {
              return b
            }).length,
            i = !1,
            j = this.elementValue(b)
          'function' == typeof g.normalizer
            ? (f = g.normalizer)
            : 'function' == typeof this.settings.normalizer &&
              (f = this.settings.normalizer),
            f && ((j = f.call(b, j)), delete g.normalizer)
          for (d in g) {
            e = { method: d, parameters: g[d] }
            try {
              if (
                ((c = a.validator.methods[d].call(this, j, b, e.parameters)),
                'dependency-mismatch' === c && 1 === h)
              ) {
                i = !0
                continue
              }
              if (((i = !1), 'pending' === c))
                return void (this.toHide = this.toHide.not(this.errorsFor(b)))
              if (!c) return this.formatAndAdd(b, e), !1
            } catch (k) {
              throw (
                (this.settings.debug &&
                  window.console &&
                  console.log(
                    'Exception occurred when checking element ' +
                      b.id +
                      ", check the '" +
                      e.method +
                      "' method.",
                    k
                  ),
                k instanceof TypeError &&
                  (k.message +=
                    '.  Exception occurred when checking element ' +
                    b.id +
                    ", check the '" +
                    e.method +
                    "' method."),
                k)
              )
            }
          }
          if (!i) return this.objectLength(g) && this.successList.push(b), !0
        },
        customDataMessage: function (b, c) {
          return (
            a(b).data(
              'msg' + c.charAt(0).toUpperCase() + c.substring(1).toLowerCase()
            ) || a(b).data('msg')
          )
        },
        customMessage: function (a, b) {
          var c = this.settings.messages[a]
          return c && (c.constructor === String ? c : c[b])
        },
        findDefined: function () {
          for (var a = 0; a < arguments.length; a++)
            if (void 0 !== arguments[a]) return arguments[a]
        },
        defaultMessage: function (b, c) {
          'string' == typeof c && (c = { method: c })
          var d = this.findDefined(
              this.customMessage(b.name, c.method),
              this.customDataMessage(b, c.method),
              (!this.settings.ignoreTitle && b.title) || void 0,
              a.validator.messages[c.method],
              '<strong>Warning: No message defined for ' + b.name + '</strong>'
            ),
            e = /\$?\{(\d+)\}/g
          return (
            'function' == typeof d
              ? (d = d.call(this, c.parameters, b))
              : e.test(d) &&
                (d = a.validator.format(d.replace(e, '{$1}'), c.parameters)),
            d
          )
        },
        formatAndAdd: function (a, b) {
          var c = this.defaultMessage(a, b)
          this.errorList.push({ message: c, element: a, method: b.method }),
            (this.errorMap[a.name] = c),
            (this.submitted[a.name] = c)
        },
        addWrapper: function (a) {
          return (
            this.settings.wrapper &&
              (a = a.add(a.parent(this.settings.wrapper))),
            a
          )
        },
        defaultShowErrors: function () {
          var a, b, c
          for (a = 0; this.errorList[a]; a++)
            (c = this.errorList[a]),
              this.settings.highlight &&
                this.settings.highlight.call(
                  this,
                  c.element,
                  this.settings.errorClass,
                  this.settings.validClass
                ),
              this.showLabel(c.element, c.message)
          if (
            (this.errorList.length &&
              (this.toShow = this.toShow.add(this.containers)),
            this.settings.success)
          )
            for (a = 0; this.successList[a]; a++)
              this.showLabel(this.successList[a])
          if (this.settings.unhighlight)
            for (a = 0, b = this.validElements(); b[a]; a++)
              this.settings.unhighlight.call(
                this,
                b[a],
                this.settings.errorClass,
                this.settings.validClass
              )
          ;(this.toHide = this.toHide.not(this.toShow)),
            this.hideErrors(),
            this.addWrapper(this.toShow).show()
        },
        validElements: function () {
          return this.currentElements.not(this.invalidElements())
        },
        invalidElements: function () {
          return a(this.errorList).map(function () {
            return this.element
          })
        },
        showLabel: function (b, c) {
          var d,
            e,
            f,
            g,
            h = this.errorsFor(b),
            i = this.idOrName(b),
            j = a(b).attr('aria-describedby')
          h.length
            ? (h
                .removeClass(this.settings.validClass)
                .addClass(this.settings.errorClass),
              h.html(c))
            : ((h = a('<' + this.settings.errorElement + '>')
                .attr('id', i + '-error')
                .addClass(this.settings.errorClass)
                .html(c || '')),
              (d = h),
              this.settings.wrapper &&
                (d = h
                  .hide()
                  .show()
                  .wrap('<' + this.settings.wrapper + '/>')
                  .parent()),
              this.labelContainer.length
                ? this.labelContainer.append(d)
                : this.settings.errorPlacement
                ? this.settings.errorPlacement.call(this, d, a(b))
                : d.insertAfter(b),
              h.is('label')
                ? h.attr('for', i)
                : 0 ===
                    h.parents("label[for='" + this.escapeCssMeta(i) + "']")
                      .length &&
                  ((f = h.attr('id')),
                  j
                    ? j.match(
                        new RegExp('\\b' + this.escapeCssMeta(f) + '\\b')
                      ) || (j += ' ' + f)
                    : (j = f),
                  a(b).attr('aria-describedby', j),
                  (e = this.groups[b.name]),
                  e &&
                    ((g = this),
                    a.each(g.groups, function (b, c) {
                      c === e &&
                        a(
                          "[name='" + g.escapeCssMeta(b) + "']",
                          g.currentForm
                        ).attr('aria-describedby', h.attr('id'))
                    })))),
            !c &&
              this.settings.success &&
              (h.text(''),
              'string' == typeof this.settings.success
                ? h.addClass(this.settings.success)
                : this.settings.success(h, b)),
            (this.toShow = this.toShow.add(h))
        },
        errorsFor: function (b) {
          var c = this.escapeCssMeta(this.idOrName(b)),
            d = a(b).attr('aria-describedby'),
            e = "label[for='" + c + "'], label[for='" + c + "'] *"
          return (
            d && (e = e + ', #' + this.escapeCssMeta(d).replace(/\s+/g, ', #')),
            this.errors().filter(e)
          )
        },
        escapeCssMeta: function (a) {
          return a.replace(/([\\!"#$%&'()*+,.\/:;<=>?@\[\]^`{|}~])/g, '\\$1')
        },
        idOrName: function (a) {
          return (
            this.groups[a.name] || (this.checkable(a) ? a.name : a.id || a.name)
          )
        },
        validationTargetFor: function (b) {
          return (
            this.checkable(b) && (b = this.findByName(b.name)),
            a(b).not(this.settings.ignore)[0]
          )
        },
        checkable: function (a) {
          return /radio|checkbox/i.test(a.type)
        },
        findByName: function (b) {
          return a(this.currentForm).find(
            "[name='" + this.escapeCssMeta(b) + "']"
          )
        },
        getLength: function (b, c) {
          switch (c.nodeName.toLowerCase()) {
            case 'select':
              return a('option:selected', c).length
            case 'input':
              if (this.checkable(c))
                return this.findByName(c.name).filter(':checked').length
          }
          return b.length
        },
        depend: function (a, b) {
          return !this.dependTypes[typeof a] || this.dependTypes[typeof a](a, b)
        },
        dependTypes: {
          boolean: function (a) {
            return a
          },
          string: function (b, c) {
            return !!a(b, c.form).length
          },
          function: function (a, b) {
            return a(b)
          },
        },
        optional: function (b) {
          var c = this.elementValue(b)
          return (
            !a.validator.methods.required.call(this, c, b) &&
            'dependency-mismatch'
          )
        },
        startRequest: function (b) {
          this.pending[b.name] ||
            (this.pendingRequest++,
            a(b).addClass(this.settings.pendingClass),
            (this.pending[b.name] = !0))
        },
        stopRequest: function (b, c) {
          this.pendingRequest--,
            this.pendingRequest < 0 && (this.pendingRequest = 0),
            delete this.pending[b.name],
            a(b).removeClass(this.settings.pendingClass),
            c && 0 === this.pendingRequest && this.formSubmitted && this.form()
              ? (a(this.currentForm).submit(),
                this.submitButton &&
                  a(
                    "input:hidden[name='" + this.submitButton.name + "']",
                    this.currentForm
                  ).remove(),
                (this.formSubmitted = !1))
              : !c &&
                0 === this.pendingRequest &&
                this.formSubmitted &&
                (a(this.currentForm).triggerHandler('invalid-form', [this]),
                (this.formSubmitted = !1))
        },
        previousValue: function (b, c) {
          return (
            (c = ('string' == typeof c && c) || 'remote'),
            a.data(b, 'previousValue') ||
              a.data(b, 'previousValue', {
                old: null,
                valid: !0,
                message: this.defaultMessage(b, { method: c }),
              })
          )
        },
        destroy: function () {
          this.resetForm(),
            a(this.currentForm)
              .off('.validate')
              .removeData('validator')
              .find('.validate-equalTo-blur')
              .off('.validate-equalTo')
              .removeClass('validate-equalTo-blur')
              .find('.validate-lessThan-blur')
              .off('.validate-lessThan')
              .removeClass('validate-lessThan-blur')
              .find('.validate-lessThanEqual-blur')
              .off('.validate-lessThanEqual')
              .removeClass('validate-lessThanEqual-blur')
              .find('.validate-greaterThanEqual-blur')
              .off('.validate-greaterThanEqual')
              .removeClass('validate-greaterThanEqual-blur')
              .find('.validate-greaterThan-blur')
              .off('.validate-greaterThan')
              .removeClass('validate-greaterThan-blur')
        },
      },
      classRuleSettings: {
        required: { required: !0 },
        email: { email: !0 },
        url: { url: !0 },
        date: { date: !0 },
        dateISO: { dateISO: !0 },
        number: { number: !0 },
        digits: { digits: !0 },
        creditcard: { creditcard: !0 },
      },
      addClassRules: function (b, c) {
        b.constructor === String
          ? (this.classRuleSettings[b] = c)
          : a.extend(this.classRuleSettings, b)
      },
      classRules: function (b) {
        var c = {},
          d = a(b).attr('class')
        return (
          d &&
            a.each(d.split(' '), function () {
              this in a.validator.classRuleSettings &&
                a.extend(c, a.validator.classRuleSettings[this])
            }),
          c
        )
      },
      normalizeAttributeRule: function (a, b, c, d) {
        ;/min|max|step/.test(c) &&
          (null === b || /number|range|text/.test(b)) &&
          ((d = Number(d)), isNaN(d) && (d = void 0)),
          d || 0 === d ? (a[c] = d) : b === c && 'range' !== b && (a[c] = !0)
      },
      attributeRules: function (b) {
        var c,
          d,
          e = {},
          f = a(b),
          g = b.getAttribute('type')
        for (c in a.validator.methods)
          'required' === c
            ? ((d = b.getAttribute(c)), '' === d && (d = !0), (d = !!d))
            : (d = f.attr(c)),
            this.normalizeAttributeRule(e, g, c, d)
        return (
          e.maxlength &&
            /-1|2147483647|524288/.test(e.maxlength) &&
            delete e.maxlength,
          e
        )
      },
      dataRules: function (b) {
        var c,
          d,
          e = {},
          f = a(b),
          g = b.getAttribute('type')
        for (c in a.validator.methods)
          (d = f.data(
            'rule' + c.charAt(0).toUpperCase() + c.substring(1).toLowerCase()
          )),
            '' === d && (d = !0),
            this.normalizeAttributeRule(e, g, c, d)
        return e
      },
      staticRules: function (b) {
        var c = {},
          d = a.data(b.form, 'validator')
        return (
          d.settings.rules &&
            (c = a.validator.normalizeRule(d.settings.rules[b.name]) || {}),
          c
        )
      },
      normalizeRules: function (b, c) {
        return (
          a.each(b, function (d, e) {
            if (e === !1) return void delete b[d]
            if (e.param || e.depends) {
              var f = !0
              switch (typeof e.depends) {
                case 'string':
                  f = !!a(e.depends, c.form).length
                  break
                case 'function':
                  f = e.depends.call(c, c)
              }
              f
                ? (b[d] = void 0 === e.param || e.param)
                : (a.data(c.form, 'validator').resetElements(a(c)), delete b[d])
            }
          }),
          a.each(b, function (d, e) {
            b[d] = a.isFunction(e) && 'normalizer' !== d ? e(c) : e
          }),
          a.each(['minlength', 'maxlength'], function () {
            b[this] && (b[this] = Number(b[this]))
          }),
          a.each(['rangelength', 'range'], function () {
            var c
            b[this] &&
              (a.isArray(b[this])
                ? (b[this] = [Number(b[this][0]), Number(b[this][1])])
                : 'string' == typeof b[this] &&
                  ((c = b[this].replace(/[\[\]]/g, '').split(/[\s,]+/)),
                  (b[this] = [Number(c[0]), Number(c[1])])))
          }),
          a.validator.autoCreateRanges &&
            (null != b.min &&
              null != b.max &&
              ((b.range = [b.min, b.max]), delete b.min, delete b.max),
            null != b.minlength &&
              null != b.maxlength &&
              ((b.rangelength = [b.minlength, b.maxlength]),
              delete b.minlength,
              delete b.maxlength)),
          b
        )
      },
      normalizeRule: function (b) {
        if ('string' == typeof b) {
          var c = {}
          a.each(b.split(/\s/), function () {
            c[this] = !0
          }),
            (b = c)
        }
        return b
      },
      addMethod: function (b, c, d) {
        ;(a.validator.methods[b] = c),
          (a.validator.messages[b] =
            void 0 !== d ? d : a.validator.messages[b]),
          c.length < 3 &&
            a.validator.addClassRules(b, a.validator.normalizeRule(b))
      },
      methods: {
        required: function (b, c, d) {
          if (!this.depend(d, c)) return 'dependency-mismatch'
          if ('select' === c.nodeName.toLowerCase()) {
            var e = a(c).val()
            return e && e.length > 0
          }
          return this.checkable(c)
            ? this.getLength(b, c) > 0
            : void 0 !== b && null !== b && b.length > 0
        },
        email: function (a, b) {
          return (
            this.optional(b) ||
            /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/.test(
              a
            )
          )
        },
        url: function (a, b) {
          return (
            this.optional(b) ||
            /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[\/?#]\S*)?$/i.test(
              a
            )
          )
        },
        date: (function () {
          var a = !1
          return function (b, c) {
            return (
              a ||
                ((a = !0),
                this.settings.debug &&
                  window.console &&
                  console.warn(
                    "The `date` method is deprecated and will be removed in version '2.0.0'.\nPlease don't use it, since it relies on the Date constructor, which\nbehaves very differently across browsers and locales. Use `dateISO`\ninstead or one of the locale specific methods in `localizations/`\nand `additional-methods.js`."
                  )),
              this.optional(c) || !/Invalid|NaN/.test(new Date(b).toString())
            )
          }
        })(),
        dateISO: function (a, b) {
          return (
            this.optional(b) ||
            /^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$/.test(
              a
            )
          )
        },
        number: function (a, b) {
          return (
            this.optional(b) ||
            /^(?:-?\d+|-?\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(a)
          )
        },
        digits: function (a, b) {
          return this.optional(b) || /^\d+$/.test(a)
        },
        minlength: function (b, c, d) {
          var e = a.isArray(b) ? b.length : this.getLength(b, c)
          return this.optional(c) || e >= d
        },
        maxlength: function (b, c, d) {
          var e = a.isArray(b) ? b.length : this.getLength(b, c)
          return this.optional(c) || e <= d
        },
        rangelength: function (b, c, d) {
          var e = a.isArray(b) ? b.length : this.getLength(b, c)
          return this.optional(c) || (e >= d[0] && e <= d[1])
        },
        min: function (a, b, c) {
          return this.optional(b) || a >= c
        },
        max: function (a, b, c) {
          return this.optional(b) || a <= c
        },
        range: function (a, b, c) {
          return this.optional(b) || (a >= c[0] && a <= c[1])
        },
        step: function (b, c, d) {
          var e,
            f = a(c).attr('type'),
            g = 'Step attribute on input type ' + f + ' is not supported.',
            h = ['text', 'number', 'range'],
            i = new RegExp('\\b' + f + '\\b'),
            j = f && !i.test(h.join()),
            k = function (a) {
              var b = ('' + a).match(/(?:\.(\d+))?$/)
              return b && b[1] ? b[1].length : 0
            },
            l = function (a) {
              return Math.round(a * Math.pow(10, e))
            },
            m = !0
          if (j) throw new Error(g)
          return (
            (e = k(d)),
            (k(b) > e || l(b) % l(d) !== 0) && (m = !1),
            this.optional(c) || m
          )
        },
        equalTo: function (b, c, d) {
          var e = a(d)
          return (
            this.settings.onfocusout &&
              e.not('.validate-equalTo-blur').length &&
              e
                .addClass('validate-equalTo-blur')
                .on('blur.validate-equalTo', function () {
                  a(c).valid()
                }),
            b === e.val()
          )
        },
        remote: function (b, c, d, e) {
          if (this.optional(c)) return 'dependency-mismatch'
          e = ('string' == typeof e && e) || 'remote'
          var f,
            g,
            h,
            i = this.previousValue(c, e)
          return (
            this.settings.messages[c.name] ||
              (this.settings.messages[c.name] = {}),
            (i.originalMessage =
              i.originalMessage || this.settings.messages[c.name][e]),
            (this.settings.messages[c.name][e] = i.message),
            (d = ('string' == typeof d && { url: d }) || d),
            (h = a.param(a.extend({ data: b }, d.data))),
            i.old === h
              ? i.valid
              : ((i.old = h),
                (f = this),
                this.startRequest(c),
                (g = {}),
                (g[c.name] = b),
                a.ajax(
                  a.extend(
                    !0,
                    {
                      mode: 'abort',
                      port: 'validate' + c.name,
                      dataType: 'json',
                      data: g,
                      context: f.currentForm,
                      success: function (a) {
                        var d,
                          g,
                          h,
                          j = a === !0 || 'true' === a
                        ;(f.settings.messages[c.name][e] = i.originalMessage),
                          j
                            ? ((h = f.formSubmitted),
                              f.resetInternals(),
                              (f.toHide = f.errorsFor(c)),
                              (f.formSubmitted = h),
                              f.successList.push(c),
                              (f.invalid[c.name] = !1),
                              f.showErrors())
                            : ((d = {}),
                              (g =
                                a ||
                                f.defaultMessage(c, {
                                  method: e,
                                  parameters: b,
                                })),
                              (d[c.name] = i.message = g),
                              (f.invalid[c.name] = !0),
                              f.showErrors(d)),
                          (i.valid = j),
                          f.stopRequest(c, j)
                      },
                    },
                    d
                  )
                ),
                'pending')
          )
        },
      },
    })
  var b,
    c = {}
  return (
    a.ajaxPrefilter
      ? a.ajaxPrefilter(function (a, b, d) {
          var e = a.port
          'abort' === a.mode && (c[e] && c[e].abort(), (c[e] = d))
        })
      : ((b = a.ajax),
        (a.ajax = function (d) {
          var e = ('mode' in d ? d : a.ajaxSettings).mode,
            f = ('port' in d ? d : a.ajaxSettings).port
          return 'abort' === e
            ? (c[f] && c[f].abort(), (c[f] = b.apply(this, arguments)), c[f])
            : b.apply(this, arguments)
        })),
    a
  )
})
/**
 * Swiper 5.2.1
 * Most modern mobile touch slider and framework with hardware accelerated transitions
 * http://swiperjs.com
 *
 * Copyright 2014-2019 Vladimir Kharlampidi
 *
 * Released under the MIT License
 *
 * Released on: November 16, 2019
 */

!(function (e, t) {
  'object' == typeof exports && 'undefined' != typeof module
    ? (module.exports = t())
    : 'function' == typeof define && define.amd
    ? define(t)
    : ((e = e || self).Swiper = t())
})(this, function () {
  'use strict'
  var e =
      'undefined' == typeof document
        ? {
            body: {},
            addEventListener: function () {},
            removeEventListener: function () {},
            activeElement: { blur: function () {}, nodeName: '' },
            querySelector: function () {
              return null
            },
            querySelectorAll: function () {
              return []
            },
            getElementById: function () {
              return null
            },
            createEvent: function () {
              return { initEvent: function () {} }
            },
            createElement: function () {
              return {
                children: [],
                childNodes: [],
                style: {},
                setAttribute: function () {},
                getElementsByTagName: function () {
                  return []
                },
              }
            },
            location: { hash: '' },
          }
        : document,
    t =
      'undefined' == typeof window
        ? {
            document: e,
            navigator: { userAgent: '' },
            location: {},
            history: {},
            CustomEvent: function () {
              return this
            },
            addEventListener: function () {},
            removeEventListener: function () {},
            getComputedStyle: function () {
              return {
                getPropertyValue: function () {
                  return ''
                },
              }
            },
            Image: function () {},
            Date: function () {},
            screen: {},
            setTimeout: function () {},
            clearTimeout: function () {},
          }
        : window,
    i = function (e) {
      for (var t = 0; t < e.length; t += 1) this[t] = e[t]
      return (this.length = e.length), this
    }
  function s(s, a) {
    var r = [],
      n = 0
    if (s && !a && s instanceof i) return s
    if (s)
      if ('string' == typeof s) {
        var o,
          l,
          d = s.trim()
        if (d.indexOf('<') >= 0 && d.indexOf('>') >= 0) {
          var h = 'div'
          for (
            0 === d.indexOf('<li') && (h = 'ul'),
              0 === d.indexOf('<tr') && (h = 'tbody'),
              (0 !== d.indexOf('<td') && 0 !== d.indexOf('<th')) || (h = 'tr'),
              0 === d.indexOf('<tbody') && (h = 'table'),
              0 === d.indexOf('<option') && (h = 'select'),
              (l = e.createElement(h)).innerHTML = d,
              n = 0;
            n < l.childNodes.length;
            n += 1
          )
            r.push(l.childNodes[n])
        } else
          for (
            o =
              a || '#' !== s[0] || s.match(/[ .<>:~]/)
                ? (a || e).querySelectorAll(s.trim())
                : [e.getElementById(s.trim().split('#')[1])],
              n = 0;
            n < o.length;
            n += 1
          )
            o[n] && r.push(o[n])
      } else if (s.nodeType || s === t || s === e) r.push(s)
      else if (s.length > 0 && s[0].nodeType)
        for (n = 0; n < s.length; n += 1) r.push(s[n])
    return new i(r)
  }
  function a(e) {
    for (var t = [], i = 0; i < e.length; i += 1)
      -1 === t.indexOf(e[i]) && t.push(e[i])
    return t
  }
  ;(s.fn = i.prototype), (s.Class = i), (s.Dom7 = i)
  var r = {
    addClass: function (e) {
      if (void 0 === e) return this
      for (var t = e.split(' '), i = 0; i < t.length; i += 1)
        for (var s = 0; s < this.length; s += 1)
          void 0 !== this[s] &&
            void 0 !== this[s].classList &&
            this[s].classList.add(t[i])
      return this
    },
    removeClass: function (e) {
      for (var t = e.split(' '), i = 0; i < t.length; i += 1)
        for (var s = 0; s < this.length; s += 1)
          void 0 !== this[s] &&
            void 0 !== this[s].classList &&
            this[s].classList.remove(t[i])
      return this
    },
    hasClass: function (e) {
      return !!this[0] && this[0].classList.contains(e)
    },
    toggleClass: function (e) {
      for (var t = e.split(' '), i = 0; i < t.length; i += 1)
        for (var s = 0; s < this.length; s += 1)
          void 0 !== this[s] &&
            void 0 !== this[s].classList &&
            this[s].classList.toggle(t[i])
      return this
    },
    attr: function (e, t) {
      var i = arguments
      if (1 === arguments.length && 'string' == typeof e)
        return this[0] ? this[0].getAttribute(e) : void 0
      for (var s = 0; s < this.length; s += 1)
        if (2 === i.length) this[s].setAttribute(e, t)
        else for (var a in e) (this[s][a] = e[a]), this[s].setAttribute(a, e[a])
      return this
    },
    removeAttr: function (e) {
      for (var t = 0; t < this.length; t += 1) this[t].removeAttribute(e)
      return this
    },
    data: function (e, t) {
      var i
      if (void 0 !== t) {
        for (var s = 0; s < this.length; s += 1)
          (i = this[s]).dom7ElementDataStorage ||
            (i.dom7ElementDataStorage = {}),
            (i.dom7ElementDataStorage[e] = t)
        return this
      }
      if ((i = this[0])) {
        if (i.dom7ElementDataStorage && e in i.dom7ElementDataStorage)
          return i.dom7ElementDataStorage[e]
        var a = i.getAttribute('data-' + e)
        return a || void 0
      }
    },
    transform: function (e) {
      for (var t = 0; t < this.length; t += 1) {
        var i = this[t].style
        ;(i.webkitTransform = e), (i.transform = e)
      }
      return this
    },
    transition: function (e) {
      'string' != typeof e && (e += 'ms')
      for (var t = 0; t < this.length; t += 1) {
        var i = this[t].style
        ;(i.webkitTransitionDuration = e), (i.transitionDuration = e)
      }
      return this
    },
    on: function () {
      for (var e, t = [], i = arguments.length; i--; ) t[i] = arguments[i]
      var a = t[0],
        r = t[1],
        n = t[2],
        o = t[3]
      function l(e) {
        var t = e.target
        if (t) {
          var i = e.target.dom7EventData || []
          if ((i.indexOf(e) < 0 && i.unshift(e), s(t).is(r))) n.apply(t, i)
          else
            for (var a = s(t).parents(), o = 0; o < a.length; o += 1)
              s(a[o]).is(r) && n.apply(a[o], i)
        }
      }
      function d(e) {
        var t = (e && e.target && e.target.dom7EventData) || []
        t.indexOf(e) < 0 && t.unshift(e), n.apply(this, t)
      }
      'function' == typeof t[1] &&
        ((a = (e = t)[0]), (n = e[1]), (o = e[2]), (r = void 0)),
        o || (o = !1)
      for (var h, p = a.split(' '), c = 0; c < this.length; c += 1) {
        var u = this[c]
        if (r)
          for (h = 0; h < p.length; h += 1) {
            var v = p[h]
            u.dom7LiveListeners || (u.dom7LiveListeners = {}),
              u.dom7LiveListeners[v] || (u.dom7LiveListeners[v] = []),
              u.dom7LiveListeners[v].push({ listener: n, proxyListener: l }),
              u.addEventListener(v, l, o)
          }
        else
          for (h = 0; h < p.length; h += 1) {
            var f = p[h]
            u.dom7Listeners || (u.dom7Listeners = {}),
              u.dom7Listeners[f] || (u.dom7Listeners[f] = []),
              u.dom7Listeners[f].push({ listener: n, proxyListener: d }),
              u.addEventListener(f, d, o)
          }
      }
      return this
    },
    off: function () {
      for (var e, t = [], i = arguments.length; i--; ) t[i] = arguments[i]
      var s = t[0],
        a = t[1],
        r = t[2],
        n = t[3]
      'function' == typeof t[1] &&
        ((s = (e = t)[0]), (r = e[1]), (n = e[2]), (a = void 0)),
        n || (n = !1)
      for (var o = s.split(' '), l = 0; l < o.length; l += 1)
        for (var d = o[l], h = 0; h < this.length; h += 1) {
          var p = this[h],
            c = void 0
          if (
            (!a && p.dom7Listeners
              ? (c = p.dom7Listeners[d])
              : a && p.dom7LiveListeners && (c = p.dom7LiveListeners[d]),
            c && c.length)
          )
            for (var u = c.length - 1; u >= 0; u -= 1) {
              var v = c[u]
              r && v.listener === r
                ? (p.removeEventListener(d, v.proxyListener, n), c.splice(u, 1))
                : r &&
                  v.listener &&
                  v.listener.dom7proxy &&
                  v.listener.dom7proxy === r
                ? (p.removeEventListener(d, v.proxyListener, n), c.splice(u, 1))
                : r ||
                  (p.removeEventListener(d, v.proxyListener, n), c.splice(u, 1))
            }
        }
      return this
    },
    trigger: function () {
      for (var i = [], s = arguments.length; s--; ) i[s] = arguments[s]
      for (var a = i[0].split(' '), r = i[1], n = 0; n < a.length; n += 1)
        for (var o = a[n], l = 0; l < this.length; l += 1) {
          var d = this[l],
            h = void 0
          try {
            h = new t.CustomEvent(o, { detail: r, bubbles: !0, cancelable: !0 })
          } catch (t) {
            ;(h = e.createEvent('Event')).initEvent(o, !0, !0), (h.detail = r)
          }
          ;(d.dom7EventData = i.filter(function (e, t) {
            return t > 0
          })),
            d.dispatchEvent(h),
            (d.dom7EventData = []),
            delete d.dom7EventData
        }
      return this
    },
    transitionEnd: function (e) {
      var t,
        i = ['webkitTransitionEnd', 'transitionend'],
        s = this
      function a(r) {
        if (r.target === this)
          for (e.call(this, r), t = 0; t < i.length; t += 1) s.off(i[t], a)
      }
      if (e) for (t = 0; t < i.length; t += 1) s.on(i[t], a)
      return this
    },
    outerWidth: function (e) {
      if (this.length > 0) {
        if (e) {
          var t = this.styles()
          return (
            this[0].offsetWidth +
            parseFloat(t.getPropertyValue('margin-right')) +
            parseFloat(t.getPropertyValue('margin-left'))
          )
        }
        return this[0].offsetWidth
      }
      return null
    },
    outerHeight: function (e) {
      if (this.length > 0) {
        if (e) {
          var t = this.styles()
          return (
            this[0].offsetHeight +
            parseFloat(t.getPropertyValue('margin-top')) +
            parseFloat(t.getPropertyValue('margin-bottom'))
          )
        }
        return this[0].offsetHeight
      }
      return null
    },
    offset: function () {
      if (this.length > 0) {
        var i = this[0],
          s = i.getBoundingClientRect(),
          a = e.body,
          r = i.clientTop || a.clientTop || 0,
          n = i.clientLeft || a.clientLeft || 0,
          o = i === t ? t.scrollY : i.scrollTop,
          l = i === t ? t.scrollX : i.scrollLeft
        return { top: s.top + o - r, left: s.left + l - n }
      }
      return null
    },
    css: function (e, i) {
      var s
      if (1 === arguments.length) {
        if ('string' != typeof e) {
          for (s = 0; s < this.length; s += 1)
            for (var a in e) this[s].style[a] = e[a]
          return this
        }
        if (this[0])
          return t.getComputedStyle(this[0], null).getPropertyValue(e)
      }
      if (2 === arguments.length && 'string' == typeof e) {
        for (s = 0; s < this.length; s += 1) this[s].style[e] = i
        return this
      }
      return this
    },
    each: function (e) {
      if (!e) return this
      for (var t = 0; t < this.length; t += 1)
        if (!1 === e.call(this[t], t, this[t])) return this
      return this
    },
    html: function (e) {
      if (void 0 === e) return this[0] ? this[0].innerHTML : void 0
      for (var t = 0; t < this.length; t += 1) this[t].innerHTML = e
      return this
    },
    text: function (e) {
      if (void 0 === e) return this[0] ? this[0].textContent.trim() : null
      for (var t = 0; t < this.length; t += 1) this[t].textContent = e
      return this
    },
    is: function (a) {
      var r,
        n,
        o = this[0]
      if (!o || void 0 === a) return !1
      if ('string' == typeof a) {
        if (o.matches) return o.matches(a)
        if (o.webkitMatchesSelector) return o.webkitMatchesSelector(a)
        if (o.msMatchesSelector) return o.msMatchesSelector(a)
        for (r = s(a), n = 0; n < r.length; n += 1) if (r[n] === o) return !0
        return !1
      }
      if (a === e) return o === e
      if (a === t) return o === t
      if (a.nodeType || a instanceof i) {
        for (r = a.nodeType ? [a] : a, n = 0; n < r.length; n += 1)
          if (r[n] === o) return !0
        return !1
      }
      return !1
    },
    index: function () {
      var e,
        t = this[0]
      if (t) {
        for (e = 0; null !== (t = t.previousSibling); )
          1 === t.nodeType && (e += 1)
        return e
      }
    },
    eq: function (e) {
      if (void 0 === e) return this
      var t,
        s = this.length
      return new i(
        e > s - 1 ? [] : e < 0 ? ((t = s + e) < 0 ? [] : [this[t]]) : [this[e]]
      )
    },
    append: function () {
      for (var t, s = [], a = arguments.length; a--; ) s[a] = arguments[a]
      for (var r = 0; r < s.length; r += 1) {
        t = s[r]
        for (var n = 0; n < this.length; n += 1)
          if ('string' == typeof t) {
            var o = e.createElement('div')
            for (o.innerHTML = t; o.firstChild; )
              this[n].appendChild(o.firstChild)
          } else if (t instanceof i)
            for (var l = 0; l < t.length; l += 1) this[n].appendChild(t[l])
          else this[n].appendChild(t)
      }
      return this
    },
    prepend: function (t) {
      var s, a
      for (s = 0; s < this.length; s += 1)
        if ('string' == typeof t) {
          var r = e.createElement('div')
          for (r.innerHTML = t, a = r.childNodes.length - 1; a >= 0; a -= 1)
            this[s].insertBefore(r.childNodes[a], this[s].childNodes[0])
        } else if (t instanceof i)
          for (a = 0; a < t.length; a += 1)
            this[s].insertBefore(t[a], this[s].childNodes[0])
        else this[s].insertBefore(t, this[s].childNodes[0])
      return this
    },
    next: function (e) {
      return this.length > 0
        ? e
          ? this[0].nextElementSibling && s(this[0].nextElementSibling).is(e)
            ? new i([this[0].nextElementSibling])
            : new i([])
          : this[0].nextElementSibling
          ? new i([this[0].nextElementSibling])
          : new i([])
        : new i([])
    },
    nextAll: function (e) {
      var t = [],
        a = this[0]
      if (!a) return new i([])
      for (; a.nextElementSibling; ) {
        var r = a.nextElementSibling
        e ? s(r).is(e) && t.push(r) : t.push(r), (a = r)
      }
      return new i(t)
    },
    prev: function (e) {
      if (this.length > 0) {
        var t = this[0]
        return e
          ? t.previousElementSibling && s(t.previousElementSibling).is(e)
            ? new i([t.previousElementSibling])
            : new i([])
          : t.previousElementSibling
          ? new i([t.previousElementSibling])
          : new i([])
      }
      return new i([])
    },
    prevAll: function (e) {
      var t = [],
        a = this[0]
      if (!a) return new i([])
      for (; a.previousElementSibling; ) {
        var r = a.previousElementSibling
        e ? s(r).is(e) && t.push(r) : t.push(r), (a = r)
      }
      return new i(t)
    },
    parent: function (e) {
      for (var t = [], i = 0; i < this.length; i += 1)
        null !== this[i].parentNode &&
          (e
            ? s(this[i].parentNode).is(e) && t.push(this[i].parentNode)
            : t.push(this[i].parentNode))
      return s(a(t))
    },
    parents: function (e) {
      for (var t = [], i = 0; i < this.length; i += 1)
        for (var r = this[i].parentNode; r; )
          e ? s(r).is(e) && t.push(r) : t.push(r), (r = r.parentNode)
      return s(a(t))
    },
    closest: function (e) {
      var t = this
      return void 0 === e ? new i([]) : (t.is(e) || (t = t.parents(e).eq(0)), t)
    },
    find: function (e) {
      for (var t = [], s = 0; s < this.length; s += 1)
        for (var a = this[s].querySelectorAll(e), r = 0; r < a.length; r += 1)
          t.push(a[r])
      return new i(t)
    },
    children: function (e) {
      for (var t = [], r = 0; r < this.length; r += 1)
        for (var n = this[r].childNodes, o = 0; o < n.length; o += 1)
          e
            ? 1 === n[o].nodeType && s(n[o]).is(e) && t.push(n[o])
            : 1 === n[o].nodeType && t.push(n[o])
      return new i(a(t))
    },
    filter: function (e) {
      for (var t = [], s = 0; s < this.length; s += 1)
        e.call(this[s], s, this[s]) && t.push(this[s])
      return new i(t)
    },
    remove: function () {
      for (var e = 0; e < this.length; e += 1)
        this[e].parentNode && this[e].parentNode.removeChild(this[e])
      return this
    },
    add: function () {
      for (var e = [], t = arguments.length; t--; ) e[t] = arguments[t]
      var i, a
      for (i = 0; i < e.length; i += 1) {
        var r = s(e[i])
        for (a = 0; a < r.length; a += 1)
          (this[this.length] = r[a]), (this.length += 1)
      }
      return this
    },
    styles: function () {
      return this[0] ? t.getComputedStyle(this[0], null) : {}
    },
  }
  Object.keys(r).forEach(function (e) {
    s.fn[e] = s.fn[e] || r[e]
  })
  var n = {
      deleteProps: function (e) {
        var t = e
        Object.keys(t).forEach(function (e) {
          try {
            t[e] = null
          } catch (e) {}
          try {
            delete t[e]
          } catch (e) {}
        })
      },
      nextTick: function (e, t) {
        return void 0 === t && (t = 0), setTimeout(e, t)
      },
      now: function () {
        return Date.now()
      },
      getTranslate: function (e, i) {
        var s, a, r
        void 0 === i && (i = 'x')
        var n = t.getComputedStyle(e, null)
        return (
          t.WebKitCSSMatrix
            ? ((a = n.transform || n.webkitTransform).split(',').length > 6 &&
                (a = a
                  .split(', ')
                  .map(function (e) {
                    return e.replace(',', '.')
                  })
                  .join(', ')),
              (r = new t.WebKitCSSMatrix('none' === a ? '' : a)))
            : (s = (r =
                n.MozTransform ||
                n.OTransform ||
                n.MsTransform ||
                n.msTransform ||
                n.transform ||
                n
                  .getPropertyValue('transform')
                  .replace('translate(', 'matrix(1, 0, 0, 1,'))
                .toString()
                .split(',')),
          'x' === i &&
            (a = t.WebKitCSSMatrix
              ? r.m41
              : 16 === s.length
              ? parseFloat(s[12])
              : parseFloat(s[4])),
          'y' === i &&
            (a = t.WebKitCSSMatrix
              ? r.m42
              : 16 === s.length
              ? parseFloat(s[13])
              : parseFloat(s[5])),
          a || 0
        )
      },
      parseUrlQuery: function (e) {
        var i,
          s,
          a,
          r,
          n = {},
          o = e || t.location.href
        if ('string' == typeof o && o.length)
          for (
            r = (s = (o = o.indexOf('?') > -1 ? o.replace(/\S*\?/, '') : '')
              .split('&')
              .filter(function (e) {
                return '' !== e
              })).length,
              i = 0;
            i < r;
            i += 1
          )
            (a = s[i].replace(/#\S+/g, '').split('=')),
              (n[decodeURIComponent(a[0])] =
                void 0 === a[1] ? void 0 : decodeURIComponent(a[1]) || '')
        return n
      },
      isObject: function (e) {
        return (
          'object' == typeof e &&
          null !== e &&
          e.constructor &&
          e.constructor === Object
        )
      },
      extend: function () {
        for (var e = [], t = arguments.length; t--; ) e[t] = arguments[t]
        for (var i = Object(e[0]), s = 1; s < e.length; s += 1) {
          var a = e[s]
          if (null != a)
            for (
              var r = Object.keys(Object(a)), o = 0, l = r.length;
              o < l;
              o += 1
            ) {
              var d = r[o],
                h = Object.getOwnPropertyDescriptor(a, d)
              void 0 !== h &&
                h.enumerable &&
                (n.isObject(i[d]) && n.isObject(a[d])
                  ? n.extend(i[d], a[d])
                  : !n.isObject(i[d]) && n.isObject(a[d])
                  ? ((i[d] = {}), n.extend(i[d], a[d]))
                  : (i[d] = a[d]))
            }
        }
        return i
      },
    },
    o = {
      touch:
        (t.Modernizr && !0 === t.Modernizr.touch) ||
        !!(
          t.navigator.maxTouchPoints > 0 ||
          'ontouchstart' in t ||
          (t.DocumentTouch && e instanceof t.DocumentTouch)
        ),
      pointerEvents:
        !!t.PointerEvent &&
        'maxTouchPoints' in t.navigator &&
        t.navigator.maxTouchPoints > 0,
      observer: 'MutationObserver' in t || 'WebkitMutationObserver' in t,
      passiveListener: (function () {
        var e = !1
        try {
          var i = Object.defineProperty({}, 'passive', {
            get: function () {
              e = !0
            },
          })
          t.addEventListener('testPassiveListener', null, i)
        } catch (e) {}
        return e
      })(),
      gestures: 'ongesturestart' in t,
    },
    l = function (e) {
      void 0 === e && (e = {})
      var t = this
      ;(t.params = e),
        (t.eventsListeners = {}),
        t.params &&
          t.params.on &&
          Object.keys(t.params.on).forEach(function (e) {
            t.on(e, t.params.on[e])
          })
    },
    d = { components: { configurable: !0 } }
  ;(l.prototype.on = function (e, t, i) {
    var s = this
    if ('function' != typeof t) return s
    var a = i ? 'unshift' : 'push'
    return (
      e.split(' ').forEach(function (e) {
        s.eventsListeners[e] || (s.eventsListeners[e] = []),
          s.eventsListeners[e][a](t)
      }),
      s
    )
  }),
    (l.prototype.once = function (e, t, i) {
      var s = this
      if ('function' != typeof t) return s
      function a() {
        for (var i = [], r = arguments.length; r--; ) i[r] = arguments[r]
        s.off(e, a), a.f7proxy && delete a.f7proxy, t.apply(s, i)
      }
      return (a.f7proxy = t), s.on(e, a, i)
    }),
    (l.prototype.off = function (e, t) {
      var i = this
      return i.eventsListeners
        ? (e.split(' ').forEach(function (e) {
            void 0 === t
              ? (i.eventsListeners[e] = [])
              : i.eventsListeners[e] &&
                i.eventsListeners[e].length &&
                i.eventsListeners[e].forEach(function (s, a) {
                  ;(s === t || (s.f7proxy && s.f7proxy === t)) &&
                    i.eventsListeners[e].splice(a, 1)
                })
          }),
          i)
        : i
    }),
    (l.prototype.emit = function () {
      for (var e = [], t = arguments.length; t--; ) e[t] = arguments[t]
      var i,
        s,
        a,
        r = this
      if (!r.eventsListeners) return r
      'string' == typeof e[0] || Array.isArray(e[0])
        ? ((i = e[0]), (s = e.slice(1, e.length)), (a = r))
        : ((i = e[0].events), (s = e[0].data), (a = e[0].context || r))
      var n = Array.isArray(i) ? i : i.split(' ')
      return (
        n.forEach(function (e) {
          if (r.eventsListeners && r.eventsListeners[e]) {
            var t = []
            r.eventsListeners[e].forEach(function (e) {
              t.push(e)
            }),
              t.forEach(function (e) {
                e.apply(a, s)
              })
          }
        }),
        r
      )
    }),
    (l.prototype.useModulesParams = function (e) {
      var t = this
      t.modules &&
        Object.keys(t.modules).forEach(function (i) {
          var s = t.modules[i]
          s.params && n.extend(e, s.params)
        })
    }),
    (l.prototype.useModules = function (e) {
      void 0 === e && (e = {})
      var t = this
      t.modules &&
        Object.keys(t.modules).forEach(function (i) {
          var s = t.modules[i],
            a = e[i] || {}
          s.instance &&
            Object.keys(s.instance).forEach(function (e) {
              var i = s.instance[e]
              t[e] = 'function' == typeof i ? i.bind(t) : i
            }),
            s.on &&
              t.on &&
              Object.keys(s.on).forEach(function (e) {
                t.on(e, s.on[e])
              }),
            s.create && s.create.bind(t)(a)
        })
    }),
    (d.components.set = function (e) {
      this.use && this.use(e)
    }),
    (l.installModule = function (e) {
      for (var t = [], i = arguments.length - 1; i-- > 0; )
        t[i] = arguments[i + 1]
      var s = this
      s.prototype.modules || (s.prototype.modules = {})
      var a = e.name || Object.keys(s.prototype.modules).length + '_' + n.now()
      return (
        (s.prototype.modules[a] = e),
        e.proto &&
          Object.keys(e.proto).forEach(function (t) {
            s.prototype[t] = e.proto[t]
          }),
        e.static &&
          Object.keys(e.static).forEach(function (t) {
            s[t] = e.static[t]
          }),
        e.install && e.install.apply(s, t),
        s
      )
    }),
    (l.use = function (e) {
      for (var t = [], i = arguments.length - 1; i-- > 0; )
        t[i] = arguments[i + 1]
      var s = this
      return Array.isArray(e)
        ? (e.forEach(function (e) {
            return s.installModule(e)
          }),
          s)
        : s.installModule.apply(s, [e].concat(t))
    }),
    Object.defineProperties(l, d)
  var h = {
    updateSize: function () {
      var e,
        t,
        i = this.$el
      ;(e =
        void 0 !== this.params.width ? this.params.width : i[0].clientWidth),
        (t =
          void 0 !== this.params.height
            ? this.params.height
            : i[0].clientHeight),
        (0 === e && this.isHorizontal()) ||
          (0 === t && this.isVertical()) ||
          ((e =
            e -
            parseInt(i.css('padding-left'), 10) -
            parseInt(i.css('padding-right'), 10)),
          (t =
            t -
            parseInt(i.css('padding-top'), 10) -
            parseInt(i.css('padding-bottom'), 10)),
          n.extend(this, {
            width: e,
            height: t,
            size: this.isHorizontal() ? e : t,
          }))
    },
    updateSlides: function () {
      var e = this.params,
        i = this.$wrapperEl,
        s = this.size,
        a = this.rtlTranslate,
        r = this.wrongRTL,
        o = this.virtual && e.virtual.enabled,
        l = o ? this.virtual.slides.length : this.slides.length,
        d = i.children('.' + this.params.slideClass),
        h = o ? this.virtual.slides.length : d.length,
        p = [],
        c = [],
        u = []
      function v(t) {
        return !e.cssMode || t !== d.length - 1
      }
      var f = e.slidesOffsetBefore
      'function' == typeof f && (f = e.slidesOffsetBefore.call(this))
      var m = e.slidesOffsetAfter
      'function' == typeof m && (m = e.slidesOffsetAfter.call(this))
      var g = this.snapGrid.length,
        b = this.snapGrid.length,
        w = e.spaceBetween,
        y = -f,
        x = 0,
        T = 0
      if (void 0 !== s) {
        var E, S
        'string' == typeof w &&
          w.indexOf('%') >= 0 &&
          (w = (parseFloat(w.replace('%', '')) / 100) * s),
          (this.virtualSize = -w),
          a
            ? d.css({ marginLeft: '', marginTop: '' })
            : d.css({ marginRight: '', marginBottom: '' }),
          e.slidesPerColumn > 1 &&
            ((E =
              Math.floor(h / e.slidesPerColumn) ===
              h / this.params.slidesPerColumn
                ? h
                : Math.ceil(h / e.slidesPerColumn) * e.slidesPerColumn),
            'auto' !== e.slidesPerView &&
              'row' === e.slidesPerColumnFill &&
              (E = Math.max(E, e.slidesPerView * e.slidesPerColumn)))
        for (
          var C,
            M = e.slidesPerColumn,
            P = E / M,
            z = Math.floor(h / e.slidesPerColumn),
            k = 0;
          k < h;
          k += 1
        ) {
          S = 0
          var $ = d.eq(k)
          if (e.slidesPerColumn > 1) {
            var L = void 0,
              I = void 0,
              D = void 0
            if ('row' === e.slidesPerColumnFill && e.slidesPerGroup > 1) {
              var O = Math.floor(k / (e.slidesPerGroup * e.slidesPerColumn)),
                A = k - e.slidesPerColumn * e.slidesPerGroup * O,
                G =
                  0 === O
                    ? e.slidesPerGroup
                    : Math.min(
                        Math.ceil((h - O * M * e.slidesPerGroup) / M),
                        e.slidesPerGroup
                      )
              ;(L =
                (I = A - (D = Math.floor(A / G)) * G + O * e.slidesPerGroup) +
                (D * E) / M),
                $.css({
                  '-webkit-box-ordinal-group': L,
                  '-moz-box-ordinal-group': L,
                  '-ms-flex-order': L,
                  '-webkit-order': L,
                  order: L,
                })
            } else
              'column' === e.slidesPerColumnFill
                ? ((D = k - (I = Math.floor(k / M)) * M),
                  (I > z || (I === z && D === M - 1)) &&
                    (D += 1) >= M &&
                    ((D = 0), (I += 1)))
                : (I = k - (D = Math.floor(k / P)) * P)
            $.css(
              'margin-' + (this.isHorizontal() ? 'top' : 'left'),
              0 !== D && e.spaceBetween && e.spaceBetween + 'px'
            )
          }
          if ('none' !== $.css('display')) {
            if ('auto' === e.slidesPerView) {
              var B = t.getComputedStyle($[0], null),
                H = $[0].style.transform,
                N = $[0].style.webkitTransform
              if (
                (H && ($[0].style.transform = 'none'),
                N && ($[0].style.webkitTransform = 'none'),
                e.roundLengths)
              )
                S = this.isHorizontal() ? $.outerWidth(!0) : $.outerHeight(!0)
              else if (this.isHorizontal()) {
                var X = parseFloat(B.getPropertyValue('width')),
                  V = parseFloat(B.getPropertyValue('padding-left')),
                  Y = parseFloat(B.getPropertyValue('padding-right')),
                  F = parseFloat(B.getPropertyValue('margin-left')),
                  W = parseFloat(B.getPropertyValue('margin-right')),
                  R = B.getPropertyValue('box-sizing')
                S = R && 'border-box' === R ? X + F + W : X + V + Y + F + W
              } else {
                var q = parseFloat(B.getPropertyValue('height')),
                  j = parseFloat(B.getPropertyValue('padding-top')),
                  K = parseFloat(B.getPropertyValue('padding-bottom')),
                  U = parseFloat(B.getPropertyValue('margin-top')),
                  _ = parseFloat(B.getPropertyValue('margin-bottom')),
                  Z = B.getPropertyValue('box-sizing')
                S = Z && 'border-box' === Z ? q + U + _ : q + j + K + U + _
              }
              H && ($[0].style.transform = H),
                N && ($[0].style.webkitTransform = N),
                e.roundLengths && (S = Math.floor(S))
            } else
              (S = (s - (e.slidesPerView - 1) * w) / e.slidesPerView),
                e.roundLengths && (S = Math.floor(S)),
                d[k] &&
                  (this.isHorizontal()
                    ? (d[k].style.width = S + 'px')
                    : (d[k].style.height = S + 'px'))
            d[k] && (d[k].swiperSlideSize = S),
              u.push(S),
              e.centeredSlides
                ? ((y = y + S / 2 + x / 2 + w),
                  0 === x && 0 !== k && (y = y - s / 2 - w),
                  0 === k && (y = y - s / 2 - w),
                  Math.abs(y) < 0.001 && (y = 0),
                  e.roundLengths && (y = Math.floor(y)),
                  T % e.slidesPerGroup == 0 && p.push(y),
                  c.push(y))
                : (e.roundLengths && (y = Math.floor(y)),
                  T % e.slidesPerGroup == 0 && p.push(y),
                  c.push(y),
                  (y = y + S + w)),
              (this.virtualSize += S + w),
              (x = S),
              (T += 1)
          }
        }
        if (
          ((this.virtualSize = Math.max(this.virtualSize, s) + m),
          a &&
            r &&
            ('slide' === e.effect || 'coverflow' === e.effect) &&
            i.css({ width: this.virtualSize + e.spaceBetween + 'px' }),
          e.setWrapperSize &&
            (this.isHorizontal()
              ? i.css({ width: this.virtualSize + e.spaceBetween + 'px' })
              : i.css({ height: this.virtualSize + e.spaceBetween + 'px' })),
          e.slidesPerColumn > 1 &&
            ((this.virtualSize = (S + e.spaceBetween) * E),
            (this.virtualSize =
              Math.ceil(this.virtualSize / e.slidesPerColumn) - e.spaceBetween),
            this.isHorizontal()
              ? i.css({ width: this.virtualSize + e.spaceBetween + 'px' })
              : i.css({ height: this.virtualSize + e.spaceBetween + 'px' }),
            e.centeredSlides))
        ) {
          C = []
          for (var Q = 0; Q < p.length; Q += 1) {
            var J = p[Q]
            e.roundLengths && (J = Math.floor(J)),
              p[Q] < this.virtualSize + p[0] && C.push(J)
          }
          p = C
        }
        if (!e.centeredSlides) {
          C = []
          for (var ee = 0; ee < p.length; ee += 1) {
            var te = p[ee]
            e.roundLengths && (te = Math.floor(te)),
              p[ee] <= this.virtualSize - s && C.push(te)
          }
          ;(p = C),
            Math.floor(this.virtualSize - s) - Math.floor(p[p.length - 1]) >
              1 && p.push(this.virtualSize - s)
        }
        if (
          (0 === p.length && (p = [0]),
          0 !== e.spaceBetween &&
            (this.isHorizontal()
              ? a
                ? d.filter(v).css({ marginLeft: w + 'px' })
                : d.filter(v).css({ marginRight: w + 'px' })
              : d.filter(v).css({ marginBottom: w + 'px' })),
          e.centeredSlides && e.centeredSlidesBounds)
        ) {
          var ie = 0
          u.forEach(function (t) {
            ie += t + (e.spaceBetween ? e.spaceBetween : 0)
          })
          var se = (ie -= e.spaceBetween) - s
          p = p.map(function (e) {
            return e < 0 ? -f : e > se ? se + m : e
          })
        }
        if (e.centerInsufficientSlides) {
          var ae = 0
          if (
            (u.forEach(function (t) {
              ae += t + (e.spaceBetween ? e.spaceBetween : 0)
            }),
            (ae -= e.spaceBetween) < s)
          ) {
            var re = (s - ae) / 2
            p.forEach(function (e, t) {
              p[t] = e - re
            }),
              c.forEach(function (e, t) {
                c[t] = e + re
              })
          }
        }
        n.extend(this, {
          slides: d,
          snapGrid: p,
          slidesGrid: c,
          slidesSizesGrid: u,
        }),
          h !== l && this.emit('slidesLengthChange'),
          p.length !== g &&
            (this.params.watchOverflow && this.checkOverflow(),
            this.emit('snapGridLengthChange')),
          c.length !== b && this.emit('slidesGridLengthChange'),
          (e.watchSlidesProgress || e.watchSlidesVisibility) &&
            this.updateSlidesOffset()
      }
    },
    updateAutoHeight: function (e) {
      var t,
        i = [],
        s = 0
      if (
        ('number' == typeof e
          ? this.setTransition(e)
          : !0 === e && this.setTransition(this.params.speed),
        'auto' !== this.params.slidesPerView && this.params.slidesPerView > 1)
      )
        for (t = 0; t < Math.ceil(this.params.slidesPerView); t += 1) {
          var a = this.activeIndex + t
          if (a > this.slides.length) break
          i.push(this.slides.eq(a)[0])
        }
      else i.push(this.slides.eq(this.activeIndex)[0])
      for (t = 0; t < i.length; t += 1)
        if (void 0 !== i[t]) {
          var r = i[t].offsetHeight
          s = r > s ? r : s
        }
      s && this.$wrapperEl.css('height', s + 'px')
    },
    updateSlidesOffset: function () {
      for (var e = this.slides, t = 0; t < e.length; t += 1)
        e[t].swiperSlideOffset = this.isHorizontal()
          ? e[t].offsetLeft
          : e[t].offsetTop
    },
    updateSlidesProgress: function (e) {
      void 0 === e && (e = (this && this.translate) || 0)
      var t = this.params,
        i = this.slides,
        a = this.rtlTranslate
      if (0 !== i.length) {
        void 0 === i[0].swiperSlideOffset && this.updateSlidesOffset()
        var r = -e
        a && (r = e),
          i.removeClass(t.slideVisibleClass),
          (this.visibleSlidesIndexes = []),
          (this.visibleSlides = [])
        for (var n = 0; n < i.length; n += 1) {
          var o = i[n],
            l =
              (r +
                (t.centeredSlides ? this.minTranslate() : 0) -
                o.swiperSlideOffset) /
              (o.swiperSlideSize + t.spaceBetween)
          if (t.watchSlidesVisibility) {
            var d = -(r - o.swiperSlideOffset),
              h = d + this.slidesSizesGrid[n]
            ;((d >= 0 && d < this.size - 1) ||
              (h > 1 && h <= this.size) ||
              (d <= 0 && h >= this.size)) &&
              (this.visibleSlides.push(o),
              this.visibleSlidesIndexes.push(n),
              i.eq(n).addClass(t.slideVisibleClass))
          }
          o.progress = a ? -l : l
        }
        this.visibleSlides = s(this.visibleSlides)
      }
    },
    updateProgress: function (e) {
      if (void 0 === e) {
        var t = this.rtlTranslate ? -1 : 1
        e = (this && this.translate && this.translate * t) || 0
      }
      var i = this.params,
        s = this.maxTranslate() - this.minTranslate(),
        a = this.progress,
        r = this.isBeginning,
        o = this.isEnd,
        l = r,
        d = o
      0 === s
        ? ((a = 0), (r = !0), (o = !0))
        : ((r = (a = (e - this.minTranslate()) / s) <= 0), (o = a >= 1)),
        n.extend(this, { progress: a, isBeginning: r, isEnd: o }),
        (i.watchSlidesProgress || i.watchSlidesVisibility) &&
          this.updateSlidesProgress(e),
        r && !l && this.emit('reachBeginning toEdge'),
        o && !d && this.emit('reachEnd toEdge'),
        ((l && !r) || (d && !o)) && this.emit('fromEdge'),
        this.emit('progress', a)
    },
    updateSlidesClasses: function () {
      var e,
        t = this.slides,
        i = this.params,
        s = this.$wrapperEl,
        a = this.activeIndex,
        r = this.realIndex,
        n = this.virtual && i.virtual.enabled
      t.removeClass(
        i.slideActiveClass +
          ' ' +
          i.slideNextClass +
          ' ' +
          i.slidePrevClass +
          ' ' +
          i.slideDuplicateActiveClass +
          ' ' +
          i.slideDuplicateNextClass +
          ' ' +
          i.slideDuplicatePrevClass
      ),
        (e = n
          ? this.$wrapperEl.find(
              '.' + i.slideClass + '[data-swiper-slide-index="' + a + '"]'
            )
          : t.eq(a)).addClass(i.slideActiveClass),
        i.loop &&
          (e.hasClass(i.slideDuplicateClass)
            ? s
                .children(
                  '.' +
                    i.slideClass +
                    ':not(.' +
                    i.slideDuplicateClass +
                    ')[data-swiper-slide-index="' +
                    r +
                    '"]'
                )
                .addClass(i.slideDuplicateActiveClass)
            : s
                .children(
                  '.' +
                    i.slideClass +
                    '.' +
                    i.slideDuplicateClass +
                    '[data-swiper-slide-index="' +
                    r +
                    '"]'
                )
                .addClass(i.slideDuplicateActiveClass))
      var o = e
        .nextAll('.' + i.slideClass)
        .eq(0)
        .addClass(i.slideNextClass)
      i.loop && 0 === o.length && (o = t.eq(0)).addClass(i.slideNextClass)
      var l = e
        .prevAll('.' + i.slideClass)
        .eq(0)
        .addClass(i.slidePrevClass)
      i.loop && 0 === l.length && (l = t.eq(-1)).addClass(i.slidePrevClass),
        i.loop &&
          (o.hasClass(i.slideDuplicateClass)
            ? s
                .children(
                  '.' +
                    i.slideClass +
                    ':not(.' +
                    i.slideDuplicateClass +
                    ')[data-swiper-slide-index="' +
                    o.attr('data-swiper-slide-index') +
                    '"]'
                )
                .addClass(i.slideDuplicateNextClass)
            : s
                .children(
                  '.' +
                    i.slideClass +
                    '.' +
                    i.slideDuplicateClass +
                    '[data-swiper-slide-index="' +
                    o.attr('data-swiper-slide-index') +
                    '"]'
                )
                .addClass(i.slideDuplicateNextClass),
          l.hasClass(i.slideDuplicateClass)
            ? s
                .children(
                  '.' +
                    i.slideClass +
                    ':not(.' +
                    i.slideDuplicateClass +
                    ')[data-swiper-slide-index="' +
                    l.attr('data-swiper-slide-index') +
                    '"]'
                )
                .addClass(i.slideDuplicatePrevClass)
            : s
                .children(
                  '.' +
                    i.slideClass +
                    '.' +
                    i.slideDuplicateClass +
                    '[data-swiper-slide-index="' +
                    l.attr('data-swiper-slide-index') +
                    '"]'
                )
                .addClass(i.slideDuplicatePrevClass))
    },
    updateActiveIndex: function (e) {
      var t,
        i = this.rtlTranslate ? this.translate : -this.translate,
        s = this.slidesGrid,
        a = this.snapGrid,
        r = this.params,
        o = this.activeIndex,
        l = this.realIndex,
        d = this.snapIndex,
        h = e
      if (void 0 === h) {
        for (var p = 0; p < s.length; p += 1)
          void 0 !== s[p + 1]
            ? i >= s[p] && i < s[p + 1] - (s[p + 1] - s[p]) / 2
              ? (h = p)
              : i >= s[p] && i < s[p + 1] && (h = p + 1)
            : i >= s[p] && (h = p)
        r.normalizeSlideIndex && (h < 0 || void 0 === h) && (h = 0)
      }
      if (
        ((t =
          a.indexOf(i) >= 0
            ? a.indexOf(i)
            : Math.floor(h / r.slidesPerGroup)) >= a.length &&
          (t = a.length - 1),
        h !== o)
      ) {
        var c = parseInt(
          this.slides.eq(h).attr('data-swiper-slide-index') || h,
          10
        )
        n.extend(this, {
          snapIndex: t,
          realIndex: c,
          previousIndex: o,
          activeIndex: h,
        }),
          this.emit('activeIndexChange'),
          this.emit('snapIndexChange'),
          l !== c && this.emit('realIndexChange'),
          (this.initialized || this.runCallbacksOnInit) &&
            this.emit('slideChange')
      } else t !== d && ((this.snapIndex = t), this.emit('snapIndexChange'))
    },
    updateClickedSlide: function (e) {
      var t = this.params,
        i = s(e.target).closest('.' + t.slideClass)[0],
        a = !1
      if (i)
        for (var r = 0; r < this.slides.length; r += 1)
          this.slides[r] === i && (a = !0)
      if (!i || !a)
        return (this.clickedSlide = void 0), void (this.clickedIndex = void 0)
      ;(this.clickedSlide = i),
        this.virtual && this.params.virtual.enabled
          ? (this.clickedIndex = parseInt(
              s(i).attr('data-swiper-slide-index'),
              10
            ))
          : (this.clickedIndex = s(i).index()),
        t.slideToClickedSlide &&
          void 0 !== this.clickedIndex &&
          this.clickedIndex !== this.activeIndex &&
          this.slideToClickedSlide()
    },
  }
  var p = {
    getTranslate: function (e) {
      void 0 === e && (e = this.isHorizontal() ? 'x' : 'y')
      var t = this.params,
        i = this.rtlTranslate,
        s = this.translate,
        a = this.$wrapperEl
      if (t.virtualTranslate) return i ? -s : s
      if (t.cssMode) return s
      var r = n.getTranslate(a[0], e)
      return i && (r = -r), r || 0
    },
    setTranslate: function (e, t) {
      var i = this.rtlTranslate,
        s = this.params,
        a = this.$wrapperEl,
        r = this.wrapperEl,
        n = this.progress,
        o = 0,
        l = 0
      this.isHorizontal() ? (o = i ? -e : e) : (l = e),
        s.roundLengths && ((o = Math.floor(o)), (l = Math.floor(l))),
        s.cssMode
          ? (r[this.isHorizontal() ? 'scrollLeft' : 'scrollTop'] =
              this.isHorizontal() ? -o : -l)
          : s.virtualTranslate ||
            a.transform('translate3d(' + o + 'px, ' + l + 'px, 0px)'),
        (this.previousTranslate = this.translate),
        (this.translate = this.isHorizontal() ? o : l)
      var d = this.maxTranslate() - this.minTranslate()
      ;(0 === d ? 0 : (e - this.minTranslate()) / d) !== n &&
        this.updateProgress(e),
        this.emit('setTranslate', this.translate, t)
    },
    minTranslate: function () {
      return -this.snapGrid[0]
    },
    maxTranslate: function () {
      return -this.snapGrid[this.snapGrid.length - 1]
    },
    translateTo: function (e, t, i, s, a) {
      var r
      void 0 === e && (e = 0),
        void 0 === t && (t = this.params.speed),
        void 0 === i && (i = !0),
        void 0 === s && (s = !0)
      var n = this,
        o = n.params,
        l = n.wrapperEl
      if (n.animating && o.preventInteractionOnTransition) return !1
      var d,
        h = n.minTranslate(),
        p = n.maxTranslate()
      if (
        ((d = s && e > h ? h : s && e < p ? p : e),
        n.updateProgress(d),
        o.cssMode)
      ) {
        var c = n.isHorizontal()
        return (
          0 === t
            ? (l[c ? 'scrollLeft' : 'scrollTop'] = -d)
            : l.scrollTo
            ? l.scrollTo(
                (((r = {})[c ? 'left' : 'top'] = -d),
                (r.behavior = 'smooth'),
                r)
              )
            : (l[c ? 'scrollLeft' : 'scrollTop'] = -d),
          !0
        )
      }
      return (
        0 === t
          ? (n.setTransition(0),
            n.setTranslate(d),
            i &&
              (n.emit('beforeTransitionStart', t, a), n.emit('transitionEnd')))
          : (n.setTransition(t),
            n.setTranslate(d),
            i &&
              (n.emit('beforeTransitionStart', t, a),
              n.emit('transitionStart')),
            n.animating ||
              ((n.animating = !0),
              n.onTranslateToWrapperTransitionEnd ||
                (n.onTranslateToWrapperTransitionEnd = function (e) {
                  n &&
                    !n.destroyed &&
                    e.target === this &&
                    (n.$wrapperEl[0].removeEventListener(
                      'transitionend',
                      n.onTranslateToWrapperTransitionEnd
                    ),
                    n.$wrapperEl[0].removeEventListener(
                      'webkitTransitionEnd',
                      n.onTranslateToWrapperTransitionEnd
                    ),
                    (n.onTranslateToWrapperTransitionEnd = null),
                    delete n.onTranslateToWrapperTransitionEnd,
                    i && n.emit('transitionEnd'))
                }),
              n.$wrapperEl[0].addEventListener(
                'transitionend',
                n.onTranslateToWrapperTransitionEnd
              ),
              n.$wrapperEl[0].addEventListener(
                'webkitTransitionEnd',
                n.onTranslateToWrapperTransitionEnd
              ))),
        !0
      )
    },
  }
  var c = {
    setTransition: function (e, t) {
      this.params.cssMode || this.$wrapperEl.transition(e),
        this.emit('setTransition', e, t)
    },
    transitionStart: function (e, t) {
      void 0 === e && (e = !0)
      var i = this.activeIndex,
        s = this.params,
        a = this.previousIndex
      if (!s.cssMode) {
        s.autoHeight && this.updateAutoHeight()
        var r = t
        if (
          (r || (r = i > a ? 'next' : i < a ? 'prev' : 'reset'),
          this.emit('transitionStart'),
          e && i !== a)
        ) {
          if ('reset' === r) return void this.emit('slideResetTransitionStart')
          this.emit('slideChangeTransitionStart'),
            'next' === r
              ? this.emit('slideNextTransitionStart')
              : this.emit('slidePrevTransitionStart')
        }
      }
    },
    transitionEnd: function (e, t) {
      void 0 === e && (e = !0)
      var i = this.activeIndex,
        s = this.previousIndex,
        a = this.params
      if (((this.animating = !1), !a.cssMode)) {
        this.setTransition(0)
        var r = t
        if (
          (r || (r = i > s ? 'next' : i < s ? 'prev' : 'reset'),
          this.emit('transitionEnd'),
          e && i !== s)
        ) {
          if ('reset' === r) return void this.emit('slideResetTransitionEnd')
          this.emit('slideChangeTransitionEnd'),
            'next' === r
              ? this.emit('slideNextTransitionEnd')
              : this.emit('slidePrevTransitionEnd')
        }
      }
    },
  }
  var u = {
    slideTo: function (e, t, i, s) {
      var a
      void 0 === e && (e = 0),
        void 0 === t && (t = this.params.speed),
        void 0 === i && (i = !0)
      var r = this,
        n = e
      n < 0 && (n = 0)
      var o = r.params,
        l = r.snapGrid,
        d = r.slidesGrid,
        h = r.previousIndex,
        p = r.activeIndex,
        c = r.rtlTranslate,
        u = r.wrapperEl
      if (r.animating && o.preventInteractionOnTransition) return !1
      var v = Math.floor(n / o.slidesPerGroup)
      v >= l.length && (v = l.length - 1),
        (p || o.initialSlide || 0) === (h || 0) &&
          i &&
          r.emit('beforeSlideChangeStart')
      var f,
        m = -l[v]
      if ((r.updateProgress(m), o.normalizeSlideIndex))
        for (var g = 0; g < d.length; g += 1)
          -Math.floor(100 * m) >= Math.floor(100 * d[g]) && (n = g)
      if (r.initialized && n !== p) {
        if (!r.allowSlideNext && m < r.translate && m < r.minTranslate())
          return !1
        if (
          !r.allowSlidePrev &&
          m > r.translate &&
          m > r.maxTranslate() &&
          (p || 0) !== n
        )
          return !1
      }
      if (
        ((f = n > p ? 'next' : n < p ? 'prev' : 'reset'),
        (c && -m === r.translate) || (!c && m === r.translate))
      )
        return (
          r.updateActiveIndex(n),
          o.autoHeight && r.updateAutoHeight(),
          r.updateSlidesClasses(),
          'slide' !== o.effect && r.setTranslate(m),
          'reset' !== f && (r.transitionStart(i, f), r.transitionEnd(i, f)),
          !1
        )
      if (o.cssMode) {
        var b = r.isHorizontal()
        return (
          0 === t
            ? (u[b ? 'scrollLeft' : 'scrollTop'] = -m)
            : u.scrollTo
            ? u.scrollTo(
                (((a = {})[b ? 'left' : 'top'] = -m),
                (a.behavior = 'smooth'),
                a)
              )
            : (u[b ? 'scrollLeft' : 'scrollTop'] = -m),
          !0
        )
      }
      return (
        0 === t
          ? (r.setTransition(0),
            r.setTranslate(m),
            r.updateActiveIndex(n),
            r.updateSlidesClasses(),
            r.emit('beforeTransitionStart', t, s),
            r.transitionStart(i, f),
            r.transitionEnd(i, f))
          : (r.setTransition(t),
            r.setTranslate(m),
            r.updateActiveIndex(n),
            r.updateSlidesClasses(),
            r.emit('beforeTransitionStart', t, s),
            r.transitionStart(i, f),
            r.animating ||
              ((r.animating = !0),
              r.onSlideToWrapperTransitionEnd ||
                (r.onSlideToWrapperTransitionEnd = function (e) {
                  r &&
                    !r.destroyed &&
                    e.target === this &&
                    (r.$wrapperEl[0].removeEventListener(
                      'transitionend',
                      r.onSlideToWrapperTransitionEnd
                    ),
                    r.$wrapperEl[0].removeEventListener(
                      'webkitTransitionEnd',
                      r.onSlideToWrapperTransitionEnd
                    ),
                    (r.onSlideToWrapperTransitionEnd = null),
                    delete r.onSlideToWrapperTransitionEnd,
                    r.transitionEnd(i, f))
                }),
              r.$wrapperEl[0].addEventListener(
                'transitionend',
                r.onSlideToWrapperTransitionEnd
              ),
              r.$wrapperEl[0].addEventListener(
                'webkitTransitionEnd',
                r.onSlideToWrapperTransitionEnd
              ))),
        !0
      )
    },
    slideToLoop: function (e, t, i, s) {
      void 0 === e && (e = 0),
        void 0 === t && (t = this.params.speed),
        void 0 === i && (i = !0)
      var a = e
      return (
        this.params.loop && (a += this.loopedSlides), this.slideTo(a, t, i, s)
      )
    },
    slideNext: function (e, t, i) {
      void 0 === e && (e = this.params.speed), void 0 === t && (t = !0)
      var s = this.params,
        a = this.animating
      return s.loop
        ? !a &&
            (this.loopFix(),
            (this._clientLeft = this.$wrapperEl[0].clientLeft),
            this.slideTo(this.activeIndex + s.slidesPerGroup, e, t, i))
        : this.slideTo(this.activeIndex + s.slidesPerGroup, e, t, i)
    },
    slidePrev: function (e, t, i) {
      void 0 === e && (e = this.params.speed), void 0 === t && (t = !0)
      var s = this.params,
        a = this.animating,
        r = this.snapGrid,
        n = this.slidesGrid,
        o = this.rtlTranslate
      if (s.loop) {
        if (a) return !1
        this.loopFix(), (this._clientLeft = this.$wrapperEl[0].clientLeft)
      }
      function l(e) {
        return e < 0 ? -Math.floor(Math.abs(e)) : Math.floor(e)
      }
      var d,
        h = l(o ? this.translate : -this.translate),
        p = r.map(function (e) {
          return l(e)
        }),
        c =
          (n.map(function (e) {
            return l(e)
          }),
          r[p.indexOf(h)],
          r[p.indexOf(h) - 1])
      return (
        void 0 === c &&
          s.cssMode &&
          r.forEach(function (e) {
            !c && h >= e && (c = e)
          }),
        void 0 !== c && (d = n.indexOf(c)) < 0 && (d = this.activeIndex - 1),
        this.slideTo(d, e, t, i)
      )
    },
    slideReset: function (e, t, i) {
      return (
        void 0 === e && (e = this.params.speed),
        void 0 === t && (t = !0),
        this.slideTo(this.activeIndex, e, t, i)
      )
    },
    slideToClosest: function (e, t, i, s) {
      void 0 === e && (e = this.params.speed),
        void 0 === t && (t = !0),
        void 0 === s && (s = 0.5)
      var a = this.activeIndex,
        r = Math.floor(a / this.params.slidesPerGroup),
        n = this.rtlTranslate ? this.translate : -this.translate
      if (n >= this.snapGrid[r]) {
        var o = this.snapGrid[r]
        n - o > (this.snapGrid[r + 1] - o) * s &&
          (a += this.params.slidesPerGroup)
      } else {
        var l = this.snapGrid[r - 1]
        n - l <= (this.snapGrid[r] - l) * s && (a -= this.params.slidesPerGroup)
      }
      return (
        (a = Math.max(a, 0)),
        (a = Math.min(a, this.snapGrid.length - 1)),
        this.slideTo(a, e, t, i)
      )
    },
    slideToClickedSlide: function () {
      var e,
        t = this,
        i = t.params,
        a = t.$wrapperEl,
        r =
          'auto' === i.slidesPerView
            ? t.slidesPerViewDynamic()
            : i.slidesPerView,
        o = t.clickedIndex
      if (i.loop) {
        if (t.animating) return
        ;(e = parseInt(s(t.clickedSlide).attr('data-swiper-slide-index'), 10)),
          i.centeredSlides
            ? o < t.loopedSlides - r / 2 ||
              o > t.slides.length - t.loopedSlides + r / 2
              ? (t.loopFix(),
                (o = a
                  .children(
                    '.' +
                      i.slideClass +
                      '[data-swiper-slide-index="' +
                      e +
                      '"]:not(.' +
                      i.slideDuplicateClass +
                      ')'
                  )
                  .eq(0)
                  .index()),
                n.nextTick(function () {
                  t.slideTo(o)
                }))
              : t.slideTo(o)
            : o > t.slides.length - r
            ? (t.loopFix(),
              (o = a
                .children(
                  '.' +
                    i.slideClass +
                    '[data-swiper-slide-index="' +
                    e +
                    '"]:not(.' +
                    i.slideDuplicateClass +
                    ')'
                )
                .eq(0)
                .index()),
              n.nextTick(function () {
                t.slideTo(o)
              }))
            : t.slideTo(o)
      } else t.slideTo(o)
    },
  }
  var v = {
    loopCreate: function () {
      var t = this,
        i = t.params,
        a = t.$wrapperEl
      a.children('.' + i.slideClass + '.' + i.slideDuplicateClass).remove()
      var r = a.children('.' + i.slideClass)
      if (i.loopFillGroupWithBlank) {
        var n = i.slidesPerGroup - (r.length % i.slidesPerGroup)
        if (n !== i.slidesPerGroup) {
          for (var o = 0; o < n; o += 1) {
            var l = s(e.createElement('div')).addClass(
              i.slideClass + ' ' + i.slideBlankClass
            )
            a.append(l)
          }
          r = a.children('.' + i.slideClass)
        }
      }
      'auto' !== i.slidesPerView ||
        i.loopedSlides ||
        (i.loopedSlides = r.length),
        (t.loopedSlides = Math.ceil(
          parseFloat(i.loopedSlides || i.slidesPerView, 10)
        )),
        (t.loopedSlides += i.loopAdditionalSlides),
        t.loopedSlides > r.length && (t.loopedSlides = r.length)
      var d = [],
        h = []
      r.each(function (e, i) {
        var a = s(i)
        e < t.loopedSlides && h.push(i),
          e < r.length && e >= r.length - t.loopedSlides && d.push(i),
          a.attr('data-swiper-slide-index', e)
      })
      for (var p = 0; p < h.length; p += 1)
        a.append(s(h[p].cloneNode(!0)).addClass(i.slideDuplicateClass))
      for (var c = d.length - 1; c >= 0; c -= 1)
        a.prepend(s(d[c].cloneNode(!0)).addClass(i.slideDuplicateClass))
    },
    loopFix: function () {
      this.emit('beforeLoopFix')
      var e,
        t = this.activeIndex,
        i = this.slides,
        s = this.loopedSlides,
        a = this.allowSlidePrev,
        r = this.allowSlideNext,
        n = this.snapGrid,
        o = this.rtlTranslate
      ;(this.allowSlidePrev = !0), (this.allowSlideNext = !0)
      var l = -n[t] - this.getTranslate()
      if (t < s)
        (e = i.length - 3 * s + t),
          (e += s),
          this.slideTo(e, 0, !1, !0) &&
            0 !== l &&
            this.setTranslate((o ? -this.translate : this.translate) - l)
      else if (t >= i.length - s) {
        ;(e = -i.length + t + s),
          (e += s),
          this.slideTo(e, 0, !1, !0) &&
            0 !== l &&
            this.setTranslate((o ? -this.translate : this.translate) - l)
      }
      ;(this.allowSlidePrev = a),
        (this.allowSlideNext = r),
        this.emit('loopFix')
    },
    loopDestroy: function () {
      var e = this.$wrapperEl,
        t = this.params,
        i = this.slides
      e
        .children(
          '.' +
            t.slideClass +
            '.' +
            t.slideDuplicateClass +
            ',.' +
            t.slideClass +
            '.' +
            t.slideBlankClass
        )
        .remove(),
        i.removeAttr('data-swiper-slide-index')
    },
  }
  var f = {
    setGrabCursor: function (e) {
      if (
        !(
          o.touch ||
          !this.params.simulateTouch ||
          (this.params.watchOverflow && this.isLocked) ||
          this.params.cssMode
        )
      ) {
        var t = this.el
        ;(t.style.cursor = 'move'),
          (t.style.cursor = e ? '-webkit-grabbing' : '-webkit-grab'),
          (t.style.cursor = e ? '-moz-grabbin' : '-moz-grab'),
          (t.style.cursor = e ? 'grabbing' : 'grab')
      }
    },
    unsetGrabCursor: function () {
      o.touch ||
        (this.params.watchOverflow && this.isLocked) ||
        this.params.cssMode ||
        (this.el.style.cursor = '')
    },
  }
  var m,
    g,
    b,
    w,
    y,
    x,
    T,
    E,
    S,
    C,
    M,
    P,
    z,
    k,
    $,
    L = {
      appendSlide: function (e) {
        var t = this.$wrapperEl,
          i = this.params
        if (
          (i.loop && this.loopDestroy(), 'object' == typeof e && 'length' in e)
        )
          for (var s = 0; s < e.length; s += 1) e[s] && t.append(e[s])
        else t.append(e)
        i.loop && this.loopCreate(), (i.observer && o.observer) || this.update()
      },
      prependSlide: function (e) {
        var t = this.params,
          i = this.$wrapperEl,
          s = this.activeIndex
        t.loop && this.loopDestroy()
        var a = s + 1
        if ('object' == typeof e && 'length' in e) {
          for (var r = 0; r < e.length; r += 1) e[r] && i.prepend(e[r])
          a = s + e.length
        } else i.prepend(e)
        t.loop && this.loopCreate(),
          (t.observer && o.observer) || this.update(),
          this.slideTo(a, 0, !1)
      },
      addSlide: function (e, t) {
        var i = this.$wrapperEl,
          s = this.params,
          a = this.activeIndex
        s.loop &&
          ((a -= this.loopedSlides),
          this.loopDestroy(),
          (this.slides = i.children('.' + s.slideClass)))
        var r = this.slides.length
        if (e <= 0) this.prependSlide(t)
        else if (e >= r) this.appendSlide(t)
        else {
          for (var n = a > e ? a + 1 : a, l = [], d = r - 1; d >= e; d -= 1) {
            var h = this.slides.eq(d)
            h.remove(), l.unshift(h)
          }
          if ('object' == typeof t && 'length' in t) {
            for (var p = 0; p < t.length; p += 1) t[p] && i.append(t[p])
            n = a > e ? a + t.length : a
          } else i.append(t)
          for (var c = 0; c < l.length; c += 1) i.append(l[c])
          s.loop && this.loopCreate(),
            (s.observer && o.observer) || this.update(),
            s.loop
              ? this.slideTo(n + this.loopedSlides, 0, !1)
              : this.slideTo(n, 0, !1)
        }
      },
      removeSlide: function (e) {
        var t = this.params,
          i = this.$wrapperEl,
          s = this.activeIndex
        t.loop &&
          ((s -= this.loopedSlides),
          this.loopDestroy(),
          (this.slides = i.children('.' + t.slideClass)))
        var a,
          r = s
        if ('object' == typeof e && 'length' in e) {
          for (var n = 0; n < e.length; n += 1)
            (a = e[n]),
              this.slides[a] && this.slides.eq(a).remove(),
              a < r && (r -= 1)
          r = Math.max(r, 0)
        } else
          (a = e),
            this.slides[a] && this.slides.eq(a).remove(),
            a < r && (r -= 1),
            (r = Math.max(r, 0))
        t.loop && this.loopCreate(),
          (t.observer && o.observer) || this.update(),
          t.loop
            ? this.slideTo(r + this.loopedSlides, 0, !1)
            : this.slideTo(r, 0, !1)
      },
      removeAllSlides: function () {
        for (var e = [], t = 0; t < this.slides.length; t += 1) e.push(t)
        this.removeSlide(e)
      },
    },
    I =
      ((m = t.navigator.platform),
      (g = t.navigator.userAgent),
      (b = {
        ios: !1,
        android: !1,
        androidChrome: !1,
        desktop: !1,
        iphone: !1,
        ipod: !1,
        ipad: !1,
        edge: !1,
        ie: !1,
        firefox: !1,
        macos: !1,
        windows: !1,
        cordova: !(!t.cordova && !t.phonegap),
        phonegap: !(!t.cordova && !t.phonegap),
        electron: !1,
      }),
      (w = t.screen.width),
      (y = t.screen.height),
      (x = g.match(/(Android);?[\s\/]+([\d.]+)?/)),
      (T = g.match(/(iPad).*OS\s([\d_]+)/)),
      (E = g.match(/(iPod)(.*OS\s([\d_]+))?/)),
      (S = !T && g.match(/(iPhone\sOS|iOS)\s([\d_]+)/)),
      (C = g.indexOf('MSIE ') >= 0 || g.indexOf('Trident/') >= 0),
      (M = g.indexOf('Edge/') >= 0),
      (P = g.indexOf('Gecko/') >= 0 && g.indexOf('Firefox/') >= 0),
      (z = 'Win32' === m),
      (k = g.toLowerCase().indexOf('electron') >= 0),
      ($ = 'MacIntel' === m),
      !T &&
        $ &&
        o.touch &&
        ((1024 === w && 1366 === y) ||
          (834 === w && 1194 === y) ||
          (834 === w && 1112 === y) ||
          (768 === w && 1024 === y)) &&
        ((T = g.match(/(Version)\/([\d.]+)/)), ($ = !1)),
      (b.ie = C),
      (b.edge = M),
      (b.firefox = P),
      x &&
        !z &&
        ((b.os = 'android'),
        (b.osVersion = x[2]),
        (b.android = !0),
        (b.androidChrome = g.toLowerCase().indexOf('chrome') >= 0)),
      (T || S || E) && ((b.os = 'ios'), (b.ios = !0)),
      S && !E && ((b.osVersion = S[2].replace(/_/g, '.')), (b.iphone = !0)),
      T && ((b.osVersion = T[2].replace(/_/g, '.')), (b.ipad = !0)),
      E &&
        ((b.osVersion = E[3] ? E[3].replace(/_/g, '.') : null), (b.ipod = !0)),
      b.ios &&
        b.osVersion &&
        g.indexOf('Version/') >= 0 &&
        '10' === b.osVersion.split('.')[0] &&
        (b.osVersion = g.toLowerCase().split('version/')[1].split(' ')[0]),
      (b.webView =
        !(
          !(S || T || E) ||
          (!g.match(/.*AppleWebKit(?!.*Safari)/i) && !t.navigator.standalone)
        ) ||
        (t.matchMedia && t.matchMedia('(display-mode: standalone)').matches)),
      (b.webview = b.webView),
      (b.standalone = b.webView),
      (b.desktop = !(b.ios || b.android) || k),
      b.desktop &&
        ((b.electron = k),
        (b.macos = $),
        (b.windows = z),
        b.macos && (b.os = 'macos'),
        b.windows && (b.os = 'windows')),
      (b.pixelRatio = t.devicePixelRatio || 1),
      b)
  function D(i) {
    var a = this.touchEventsData,
      r = this.params,
      o = this.touches
    if (!this.animating || !r.preventInteractionOnTransition) {
      var l = i
      l.originalEvent && (l = l.originalEvent)
      var d = s(l.target)
      if (
        ('wrapper' !== r.touchEventsTarget ||
          d.closest(this.wrapperEl).length) &&
        ((a.isTouchEvent = 'touchstart' === l.type),
        (a.isTouchEvent || !('which' in l) || 3 !== l.which) &&
          !(
            (!a.isTouchEvent && 'button' in l && l.button > 0) ||
            (a.isTouched && a.isMoved)
          ))
      )
        if (
          r.noSwiping &&
          d.closest(
            r.noSwipingSelector ? r.noSwipingSelector : '.' + r.noSwipingClass
          )[0]
        )
          this.allowClick = !0
        else if (!r.swipeHandler || d.closest(r.swipeHandler)[0]) {
          ;(o.currentX =
            'touchstart' === l.type ? l.targetTouches[0].pageX : l.pageX),
            (o.currentY =
              'touchstart' === l.type ? l.targetTouches[0].pageY : l.pageY)
          var h = o.currentX,
            p = o.currentY,
            c = r.edgeSwipeDetection || r.iOSEdgeSwipeDetection,
            u = r.edgeSwipeThreshold || r.iOSEdgeSwipeThreshold
          if (!c || !(h <= u || h >= t.screen.width - u)) {
            if (
              (n.extend(a, {
                isTouched: !0,
                isMoved: !1,
                allowTouchCallbacks: !0,
                isScrolling: void 0,
                startMoving: void 0,
              }),
              (o.startX = h),
              (o.startY = p),
              (a.touchStartTime = n.now()),
              (this.allowClick = !0),
              this.updateSize(),
              (this.swipeDirection = void 0),
              r.threshold > 0 && (a.allowThresholdMove = !1),
              'touchstart' !== l.type)
            ) {
              var v = !0
              d.is(a.formElements) && (v = !1),
                e.activeElement &&
                  s(e.activeElement).is(a.formElements) &&
                  e.activeElement !== d[0] &&
                  e.activeElement.blur()
              var f = v && this.allowTouchMove && r.touchStartPreventDefault
              ;(r.touchStartForcePreventDefault || f) && l.preventDefault()
            }
            this.emit('touchStart', l)
          }
        }
    }
  }
  function O(t) {
    var i = this.touchEventsData,
      a = this.params,
      r = this.touches,
      o = this.rtlTranslate,
      l = t
    if ((l.originalEvent && (l = l.originalEvent), i.isTouched)) {
      if (!i.isTouchEvent || 'mousemove' !== l.type) {
        var d =
            'touchmove' === l.type &&
            l.targetTouches &&
            (l.targetTouches[0] || l.changedTouches[0]),
          h = 'touchmove' === l.type ? d.pageX : l.pageX,
          p = 'touchmove' === l.type ? d.pageY : l.pageY
        if (l.preventedByNestedSwiper)
          return (r.startX = h), void (r.startY = p)
        if (!this.allowTouchMove)
          return (
            (this.allowClick = !1),
            void (
              i.isTouched &&
              (n.extend(r, { startX: h, startY: p, currentX: h, currentY: p }),
              (i.touchStartTime = n.now()))
            )
          )
        if (i.isTouchEvent && a.touchReleaseOnEdges && !a.loop)
          if (this.isVertical()) {
            if (
              (p < r.startY && this.translate <= this.maxTranslate()) ||
              (p > r.startY && this.translate >= this.minTranslate())
            )
              return (i.isTouched = !1), void (i.isMoved = !1)
          } else if (
            (h < r.startX && this.translate <= this.maxTranslate()) ||
            (h > r.startX && this.translate >= this.minTranslate())
          )
            return
        if (
          i.isTouchEvent &&
          e.activeElement &&
          l.target === e.activeElement &&
          s(l.target).is(i.formElements)
        )
          return (i.isMoved = !0), void (this.allowClick = !1)
        if (
          (i.allowTouchCallbacks && this.emit('touchMove', l),
          !(l.targetTouches && l.targetTouches.length > 1))
        ) {
          ;(r.currentX = h), (r.currentY = p)
          var c = r.currentX - r.startX,
            u = r.currentY - r.startY
          if (
            !(
              this.params.threshold &&
              Math.sqrt(Math.pow(c, 2) + Math.pow(u, 2)) < this.params.threshold
            )
          ) {
            var v
            if (void 0 === i.isScrolling)
              (this.isHorizontal() && r.currentY === r.startY) ||
              (this.isVertical() && r.currentX === r.startX)
                ? (i.isScrolling = !1)
                : c * c + u * u >= 25 &&
                  ((v = (180 * Math.atan2(Math.abs(u), Math.abs(c))) / Math.PI),
                  (i.isScrolling = this.isHorizontal()
                    ? v > a.touchAngle
                    : 90 - v > a.touchAngle))
            if (
              (i.isScrolling && this.emit('touchMoveOpposite', l),
              void 0 === i.startMoving &&
                ((r.currentX === r.startX && r.currentY === r.startY) ||
                  (i.startMoving = !0)),
              i.isScrolling)
            )
              i.isTouched = !1
            else if (i.startMoving) {
              ;(this.allowClick = !1),
                a.cssMode || l.preventDefault(),
                a.touchMoveStopPropagation && !a.nested && l.stopPropagation(),
                i.isMoved ||
                  (a.loop && this.loopFix(),
                  (i.startTranslate = this.getTranslate()),
                  this.setTransition(0),
                  this.animating &&
                    this.$wrapperEl.trigger(
                      'webkitTransitionEnd transitionend'
                    ),
                  (i.allowMomentumBounce = !1),
                  !a.grabCursor ||
                    (!0 !== this.allowSlideNext &&
                      !0 !== this.allowSlidePrev) ||
                    this.setGrabCursor(!0),
                  this.emit('sliderFirstMove', l)),
                this.emit('sliderMove', l),
                (i.isMoved = !0)
              var f = this.isHorizontal() ? c : u
              ;(r.diff = f),
                (f *= a.touchRatio),
                o && (f = -f),
                (this.swipeDirection = f > 0 ? 'prev' : 'next'),
                (i.currentTranslate = f + i.startTranslate)
              var m = !0,
                g = a.resistanceRatio
              if (
                (a.touchReleaseOnEdges && (g = 0),
                f > 0 && i.currentTranslate > this.minTranslate()
                  ? ((m = !1),
                    a.resistance &&
                      (i.currentTranslate =
                        this.minTranslate() -
                        1 +
                        Math.pow(
                          -this.minTranslate() + i.startTranslate + f,
                          g
                        )))
                  : f < 0 &&
                    i.currentTranslate < this.maxTranslate() &&
                    ((m = !1),
                    a.resistance &&
                      (i.currentTranslate =
                        this.maxTranslate() +
                        1 -
                        Math.pow(
                          this.maxTranslate() - i.startTranslate - f,
                          g
                        ))),
                m && (l.preventedByNestedSwiper = !0),
                !this.allowSlideNext &&
                  'next' === this.swipeDirection &&
                  i.currentTranslate < i.startTranslate &&
                  (i.currentTranslate = i.startTranslate),
                !this.allowSlidePrev &&
                  'prev' === this.swipeDirection &&
                  i.currentTranslate > i.startTranslate &&
                  (i.currentTranslate = i.startTranslate),
                a.threshold > 0)
              ) {
                if (!(Math.abs(f) > a.threshold || i.allowThresholdMove))
                  return void (i.currentTranslate = i.startTranslate)
                if (!i.allowThresholdMove)
                  return (
                    (i.allowThresholdMove = !0),
                    (r.startX = r.currentX),
                    (r.startY = r.currentY),
                    (i.currentTranslate = i.startTranslate),
                    void (r.diff = this.isHorizontal()
                      ? r.currentX - r.startX
                      : r.currentY - r.startY)
                  )
              }
              a.followFinger &&
                !a.cssMode &&
                ((a.freeMode ||
                  a.watchSlidesProgress ||
                  a.watchSlidesVisibility) &&
                  (this.updateActiveIndex(), this.updateSlidesClasses()),
                a.freeMode &&
                  (0 === i.velocities.length &&
                    i.velocities.push({
                      position: r[this.isHorizontal() ? 'startX' : 'startY'],
                      time: i.touchStartTime,
                    }),
                  i.velocities.push({
                    position: r[this.isHorizontal() ? 'currentX' : 'currentY'],
                    time: n.now(),
                  })),
                this.updateProgress(i.currentTranslate),
                this.setTranslate(i.currentTranslate))
            }
          }
        }
      }
    } else i.startMoving && i.isScrolling && this.emit('touchMoveOpposite', l)
  }
  function A(e) {
    var t = this,
      i = t.touchEventsData,
      s = t.params,
      a = t.touches,
      r = t.rtlTranslate,
      o = t.$wrapperEl,
      l = t.slidesGrid,
      d = t.snapGrid,
      h = e
    if (
      (h.originalEvent && (h = h.originalEvent),
      i.allowTouchCallbacks && t.emit('touchEnd', h),
      (i.allowTouchCallbacks = !1),
      !i.isTouched)
    )
      return (
        i.isMoved && s.grabCursor && t.setGrabCursor(!1),
        (i.isMoved = !1),
        void (i.startMoving = !1)
      )
    s.grabCursor &&
      i.isMoved &&
      i.isTouched &&
      (!0 === t.allowSlideNext || !0 === t.allowSlidePrev) &&
      t.setGrabCursor(!1)
    var p,
      c = n.now(),
      u = c - i.touchStartTime
    if (
      (t.allowClick &&
        (t.updateClickedSlide(h),
        t.emit('tap click', h),
        u < 300 &&
          c - i.lastClickTime < 300 &&
          t.emit('doubleTap doubleClick', h)),
      (i.lastClickTime = n.now()),
      n.nextTick(function () {
        t.destroyed || (t.allowClick = !0)
      }),
      !i.isTouched ||
        !i.isMoved ||
        !t.swipeDirection ||
        0 === a.diff ||
        i.currentTranslate === i.startTranslate)
    )
      return (i.isTouched = !1), (i.isMoved = !1), void (i.startMoving = !1)
    if (
      ((i.isTouched = !1),
      (i.isMoved = !1),
      (i.startMoving = !1),
      (p = s.followFinger
        ? r
          ? t.translate
          : -t.translate
        : -i.currentTranslate),
      !s.cssMode)
    )
      if (s.freeMode) {
        if (p < -t.minTranslate()) return void t.slideTo(t.activeIndex)
        if (p > -t.maxTranslate())
          return void (t.slides.length < d.length
            ? t.slideTo(d.length - 1)
            : t.slideTo(t.slides.length - 1))
        if (s.freeModeMomentum) {
          if (i.velocities.length > 1) {
            var v = i.velocities.pop(),
              f = i.velocities.pop(),
              m = v.position - f.position,
              g = v.time - f.time
            ;(t.velocity = m / g),
              (t.velocity /= 2),
              Math.abs(t.velocity) < s.freeModeMinimumVelocity &&
                (t.velocity = 0),
              (g > 150 || n.now() - v.time > 300) && (t.velocity = 0)
          } else t.velocity = 0
          ;(t.velocity *= s.freeModeMomentumVelocityRatio),
            (i.velocities.length = 0)
          var b = 1e3 * s.freeModeMomentumRatio,
            w = t.velocity * b,
            y = t.translate + w
          r && (y = -y)
          var x,
            T,
            E = !1,
            S = 20 * Math.abs(t.velocity) * s.freeModeMomentumBounceRatio
          if (y < t.maxTranslate())
            s.freeModeMomentumBounce
              ? (y + t.maxTranslate() < -S && (y = t.maxTranslate() - S),
                (x = t.maxTranslate()),
                (E = !0),
                (i.allowMomentumBounce = !0))
              : (y = t.maxTranslate()),
              s.loop && s.centeredSlides && (T = !0)
          else if (y > t.minTranslate())
            s.freeModeMomentumBounce
              ? (y - t.minTranslate() > S && (y = t.minTranslate() + S),
                (x = t.minTranslate()),
                (E = !0),
                (i.allowMomentumBounce = !0))
              : (y = t.minTranslate()),
              s.loop && s.centeredSlides && (T = !0)
          else if (s.freeModeSticky) {
            for (var C, M = 0; M < d.length; M += 1)
              if (d[M] > -y) {
                C = M
                break
              }
            y = -(y =
              Math.abs(d[C] - y) < Math.abs(d[C - 1] - y) ||
              'next' === t.swipeDirection
                ? d[C]
                : d[C - 1])
          }
          if (
            (T &&
              t.once('transitionEnd', function () {
                t.loopFix()
              }),
            0 !== t.velocity)
          ) {
            if (
              ((b = r
                ? Math.abs((-y - t.translate) / t.velocity)
                : Math.abs((y - t.translate) / t.velocity)),
              s.freeModeSticky)
            ) {
              var P = Math.abs((r ? -y : y) - t.translate),
                z = t.slidesSizesGrid[t.activeIndex]
              b = P < z ? s.speed : P < 2 * z ? 1.5 * s.speed : 2.5 * s.speed
            }
          } else if (s.freeModeSticky) return void t.slideToClosest()
          s.freeModeMomentumBounce && E
            ? (t.updateProgress(x),
              t.setTransition(b),
              t.setTranslate(y),
              t.transitionStart(!0, t.swipeDirection),
              (t.animating = !0),
              o.transitionEnd(function () {
                t &&
                  !t.destroyed &&
                  i.allowMomentumBounce &&
                  (t.emit('momentumBounce'),
                  t.setTransition(s.speed),
                  t.setTranslate(x),
                  o.transitionEnd(function () {
                    t && !t.destroyed && t.transitionEnd()
                  }))
              }))
            : t.velocity
            ? (t.updateProgress(y),
              t.setTransition(b),
              t.setTranslate(y),
              t.transitionStart(!0, t.swipeDirection),
              t.animating ||
                ((t.animating = !0),
                o.transitionEnd(function () {
                  t && !t.destroyed && t.transitionEnd()
                })))
            : t.updateProgress(y),
            t.updateActiveIndex(),
            t.updateSlidesClasses()
        } else if (s.freeModeSticky) return void t.slideToClosest()
        ;(!s.freeModeMomentum || u >= s.longSwipesMs) &&
          (t.updateProgress(), t.updateActiveIndex(), t.updateSlidesClasses())
      } else {
        for (
          var k = 0, $ = t.slidesSizesGrid[0], L = 0;
          L < l.length;
          L += s.slidesPerGroup
        )
          void 0 !== l[L + s.slidesPerGroup]
            ? p >= l[L] &&
              p < l[L + s.slidesPerGroup] &&
              ((k = L), ($ = l[L + s.slidesPerGroup] - l[L]))
            : p >= l[L] && ((k = L), ($ = l[l.length - 1] - l[l.length - 2]))
        var I = (p - l[k]) / $
        if (u > s.longSwipesMs) {
          if (!s.longSwipes) return void t.slideTo(t.activeIndex)
          'next' === t.swipeDirection &&
            (I >= s.longSwipesRatio
              ? t.slideTo(k + s.slidesPerGroup)
              : t.slideTo(k)),
            'prev' === t.swipeDirection &&
              (I > 1 - s.longSwipesRatio
                ? t.slideTo(k + s.slidesPerGroup)
                : t.slideTo(k))
        } else {
          if (!s.shortSwipes) return void t.slideTo(t.activeIndex)
          t.navigation &&
          (h.target === t.navigation.nextEl || h.target === t.navigation.prevEl)
            ? h.target === t.navigation.nextEl
              ? t.slideTo(k + s.slidesPerGroup)
              : t.slideTo(k)
            : ('next' === t.swipeDirection && t.slideTo(k + s.slidesPerGroup),
              'prev' === t.swipeDirection && t.slideTo(k))
        }
      }
  }
  function G() {
    var e = this.params,
      t = this.el
    if (!t || 0 !== t.offsetWidth) {
      e.breakpoints && this.setBreakpoint()
      var i = this.allowSlideNext,
        s = this.allowSlidePrev,
        a = this.snapGrid
      ;(this.allowSlideNext = !0),
        (this.allowSlidePrev = !0),
        this.updateSize(),
        this.updateSlides(),
        this.updateSlidesClasses(),
        ('auto' === e.slidesPerView || e.slidesPerView > 1) &&
        this.isEnd &&
        !this.params.centeredSlides
          ? this.slideTo(this.slides.length - 1, 0, !1, !0)
          : this.slideTo(this.activeIndex, 0, !1, !0),
        this.autoplay &&
          this.autoplay.running &&
          this.autoplay.paused &&
          this.autoplay.run(),
        (this.allowSlidePrev = s),
        (this.allowSlideNext = i),
        this.params.watchOverflow && a !== this.snapGrid && this.checkOverflow()
    }
  }
  function B(e) {
    this.allowClick ||
      (this.params.preventClicks && e.preventDefault(),
      this.params.preventClicksPropagation &&
        this.animating &&
        (e.stopPropagation(), e.stopImmediatePropagation()))
  }
  function H() {
    var e = this.wrapperEl
    ;(this.previousTranslate = this.translate),
      (this.translate = this.isHorizontal() ? -e.scrollLeft : -e.scrollTop),
      -0 === this.translate && (this.translate = 0),
      this.updateActiveIndex(),
      this.updateSlidesClasses()
    var t = this.maxTranslate() - this.minTranslate()
    ;(0 === t ? 0 : (this.translate - this.minTranslate()) / t) !==
      this.progress && this.updateProgress(this.translate),
      this.emit('setTranslate', this.translate, !1)
  }
  var N = !1
  function X() {}
  var V = {
      init: !0,
      direction: 'horizontal',
      touchEventsTarget: 'container',
      initialSlide: 0,
      speed: 300,
      cssMode: !1,
      updateOnWindowResize: !0,
      preventInteractionOnTransition: !1,
      edgeSwipeDetection: !1,
      edgeSwipeThreshold: 20,
      freeMode: !1,
      freeModeMomentum: !0,
      freeModeMomentumRatio: 1,
      freeModeMomentumBounce: !0,
      freeModeMomentumBounceRatio: 1,
      freeModeMomentumVelocityRatio: 1,
      freeModeSticky: !1,
      freeModeMinimumVelocity: 0.02,
      autoHeight: !1,
      setWrapperSize: !1,
      virtualTranslate: !1,
      effect: 'slide',
      breakpoints: void 0,
      spaceBetween: 0,
      slidesPerView: 1,
      slidesPerColumn: 1,
      slidesPerColumnFill: 'column',
      slidesPerGroup: 1,
      centeredSlides: !1,
      centeredSlidesBounds: !1,
      slidesOffsetBefore: 0,
      slidesOffsetAfter: 0,
      normalizeSlideIndex: !0,
      centerInsufficientSlides: !1,
      watchOverflow: !1,
      roundLengths: !1,
      touchRatio: 1,
      touchAngle: 45,
      simulateTouch: !0,
      shortSwipes: !0,
      longSwipes: !0,
      longSwipesRatio: 0.5,
      longSwipesMs: 300,
      followFinger: !0,
      allowTouchMove: !0,
      threshold: 0,
      touchMoveStopPropagation: !1,
      touchStartPreventDefault: !0,
      touchStartForcePreventDefault: !1,
      touchReleaseOnEdges: !1,
      uniqueNavElements: !0,
      resistance: !0,
      resistanceRatio: 0.85,
      watchSlidesProgress: !1,
      watchSlidesVisibility: !1,
      grabCursor: !1,
      preventClicks: !0,
      preventClicksPropagation: !0,
      slideToClickedSlide: !1,
      preloadImages: !0,
      updateOnImagesReady: !0,
      loop: !1,
      loopAdditionalSlides: 0,
      loopedSlides: null,
      loopFillGroupWithBlank: !1,
      allowSlidePrev: !0,
      allowSlideNext: !0,
      swipeHandler: null,
      noSwiping: !0,
      noSwipingClass: 'swiper-no-swiping',
      noSwipingSelector: null,
      passiveListeners: !0,
      containerModifierClass: 'swiper-container-',
      slideClass: 'swiper-slide',
      slideBlankClass: 'swiper-slide-invisible-blank',
      slideActiveClass: 'swiper-slide-active',
      slideDuplicateActiveClass: 'swiper-slide-duplicate-active',
      slideVisibleClass: 'swiper-slide-visible',
      slideDuplicateClass: 'swiper-slide-duplicate',
      slideNextClass: 'swiper-slide-next',
      slideDuplicateNextClass: 'swiper-slide-duplicate-next',
      slidePrevClass: 'swiper-slide-prev',
      slideDuplicatePrevClass: 'swiper-slide-duplicate-prev',
      wrapperClass: 'swiper-wrapper',
      runCallbacksOnInit: !0,
    },
    Y = {
      update: h,
      translate: p,
      transition: c,
      slide: u,
      loop: v,
      grabCursor: f,
      manipulation: L,
      events: {
        attachEvents: function () {
          var t = this.params,
            i = this.touchEvents,
            s = this.el,
            a = this.wrapperEl
          ;(this.onTouchStart = D.bind(this)),
            (this.onTouchMove = O.bind(this)),
            (this.onTouchEnd = A.bind(this)),
            t.cssMode && (this.onScroll = H.bind(this)),
            (this.onClick = B.bind(this))
          var r = !!t.nested
          if (!o.touch && o.pointerEvents)
            s.addEventListener(i.start, this.onTouchStart, !1),
              e.addEventListener(i.move, this.onTouchMove, r),
              e.addEventListener(i.end, this.onTouchEnd, !1)
          else {
            if (o.touch) {
              var n = !(
                'touchstart' !== i.start ||
                !o.passiveListener ||
                !t.passiveListeners
              ) && { passive: !0, capture: !1 }
              s.addEventListener(i.start, this.onTouchStart, n),
                s.addEventListener(
                  i.move,
                  this.onTouchMove,
                  o.passiveListener ? { passive: !1, capture: r } : r
                ),
                s.addEventListener(i.end, this.onTouchEnd, n),
                i.cancel && s.addEventListener(i.cancel, this.onTouchEnd, n),
                N || (e.addEventListener('touchstart', X), (N = !0))
            }
            ;((t.simulateTouch && !I.ios && !I.android) ||
              (t.simulateTouch && !o.touch && I.ios)) &&
              (s.addEventListener('mousedown', this.onTouchStart, !1),
              e.addEventListener('mousemove', this.onTouchMove, r),
              e.addEventListener('mouseup', this.onTouchEnd, !1))
          }
          ;(t.preventClicks || t.preventClicksPropagation) &&
            s.addEventListener('click', this.onClick, !0),
            t.cssMode && a.addEventListener('scroll', this.onScroll),
            t.updateOnWindowResize
              ? this.on(
                  I.ios || I.android
                    ? 'resize orientationchange observerUpdate'
                    : 'resize observerUpdate',
                  G,
                  !0
                )
              : this.on('observerUpdate', G, !0)
        },
        detachEvents: function () {
          var t = this.params,
            i = this.touchEvents,
            s = this.el,
            a = this.wrapperEl,
            r = !!t.nested
          if (!o.touch && o.pointerEvents)
            s.removeEventListener(i.start, this.onTouchStart, !1),
              e.removeEventListener(i.move, this.onTouchMove, r),
              e.removeEventListener(i.end, this.onTouchEnd, !1)
          else {
            if (o.touch) {
              var n = !(
                'onTouchStart' !== i.start ||
                !o.passiveListener ||
                !t.passiveListeners
              ) && { passive: !0, capture: !1 }
              s.removeEventListener(i.start, this.onTouchStart, n),
                s.removeEventListener(i.move, this.onTouchMove, r),
                s.removeEventListener(i.end, this.onTouchEnd, n),
                i.cancel && s.removeEventListener(i.cancel, this.onTouchEnd, n)
            }
            ;((t.simulateTouch && !I.ios && !I.android) ||
              (t.simulateTouch && !o.touch && I.ios)) &&
              (s.removeEventListener('mousedown', this.onTouchStart, !1),
              e.removeEventListener('mousemove', this.onTouchMove, r),
              e.removeEventListener('mouseup', this.onTouchEnd, !1))
          }
          ;(t.preventClicks || t.preventClicksPropagation) &&
            s.removeEventListener('click', this.onClick, !0),
            t.cssMode && a.removeEventListener('scroll', this.onScroll),
            this.off(
              I.ios || I.android
                ? 'resize orientationchange observerUpdate'
                : 'resize observerUpdate',
              G
            )
        },
      },
      breakpoints: {
        setBreakpoint: function () {
          var e = this.activeIndex,
            t = this.initialized,
            i = this.loopedSlides
          void 0 === i && (i = 0)
          var s = this.params,
            a = this.$el,
            r = s.breakpoints
          if (r && (!r || 0 !== Object.keys(r).length)) {
            var o = this.getBreakpoint(r)
            if (o && this.currentBreakpoint !== o) {
              var l = o in r ? r[o] : void 0
              l &&
                [
                  'slidesPerView',
                  'spaceBetween',
                  'slidesPerGroup',
                  'slidesPerColumn',
                ].forEach(function (e) {
                  var t = l[e]
                  void 0 !== t &&
                    (l[e] =
                      'slidesPerView' !== e || ('AUTO' !== t && 'auto' !== t)
                        ? 'slidesPerView' === e
                          ? parseFloat(t)
                          : parseInt(t, 10)
                        : 'auto')
                })
              var d = l || this.originalParams,
                h = s.slidesPerColumn > 1,
                p = d.slidesPerColumn > 1
              h && !p
                ? a.removeClass(
                    s.containerModifierClass +
                      'multirow ' +
                      s.containerModifierClass +
                      'multirow-column'
                  )
                : !h &&
                  p &&
                  (a.addClass(s.containerModifierClass + 'multirow'),
                  'column' === d.slidesPerColumnFill &&
                    a.addClass(s.containerModifierClass + 'multirow-column'))
              var c = d.direction && d.direction !== s.direction,
                u = s.loop && (d.slidesPerView !== s.slidesPerView || c)
              c && t && this.changeDirection(),
                n.extend(this.params, d),
                n.extend(this, {
                  allowTouchMove: this.params.allowTouchMove,
                  allowSlideNext: this.params.allowSlideNext,
                  allowSlidePrev: this.params.allowSlidePrev,
                }),
                (this.currentBreakpoint = o),
                u &&
                  t &&
                  (this.loopDestroy(),
                  this.loopCreate(),
                  this.updateSlides(),
                  this.slideTo(e - i + this.loopedSlides, 0, !1)),
                this.emit('breakpoint', d)
            }
          }
        },
        getBreakpoint: function (e) {
          if (e) {
            var i = !1,
              s = []
            Object.keys(e).forEach(function (e) {
              s.push(e)
            }),
              s.sort(function (e, t) {
                return parseInt(e, 10) - parseInt(t, 10)
              })
            for (var a = 0; a < s.length; a += 1) {
              var r = s[a]
              r <= t.innerWidth && (i = r)
            }
            return i || 'max'
          }
        },
      },
      checkOverflow: {
        checkOverflow: function () {
          var e = this.params,
            t = this.isLocked,
            i =
              this.slides.length > 0 &&
              e.slidesOffsetBefore +
                e.spaceBetween * (this.slides.length - 1) +
                this.slides[0].offsetWidth * this.slides.length
          e.slidesOffsetBefore && e.slidesOffsetAfter && i
            ? (this.isLocked = i <= this.size)
            : (this.isLocked = 1 === this.snapGrid.length),
            (this.allowSlideNext = !this.isLocked),
            (this.allowSlidePrev = !this.isLocked),
            t !== this.isLocked && this.emit(this.isLocked ? 'lock' : 'unlock'),
            t &&
              t !== this.isLocked &&
              ((this.isEnd = !1), this.navigation.update())
        },
      },
      classes: {
        addClasses: function () {
          var e = this.classNames,
            t = this.params,
            i = this.rtl,
            s = this.$el,
            a = []
          a.push('initialized'),
            a.push(t.direction),
            t.freeMode && a.push('free-mode'),
            t.autoHeight && a.push('autoheight'),
            i && a.push('rtl'),
            t.slidesPerColumn > 1 &&
              (a.push('multirow'),
              'column' === t.slidesPerColumnFill && a.push('multirow-column')),
            I.android && a.push('android'),
            I.ios && a.push('ios'),
            t.cssMode && a.push('css-mode'),
            a.forEach(function (i) {
              e.push(t.containerModifierClass + i)
            }),
            s.addClass(e.join(' '))
        },
        removeClasses: function () {
          var e = this.$el,
            t = this.classNames
          e.removeClass(t.join(' '))
        },
      },
      images: {
        loadImage: function (e, i, s, a, r, n) {
          var o
          function l() {
            n && n()
          }
          e.complete && r
            ? l()
            : i
            ? (((o = new t.Image()).onload = l),
              (o.onerror = l),
              a && (o.sizes = a),
              s && (o.srcset = s),
              i && (o.src = i))
            : l()
        },
        preloadImages: function () {
          var e = this
          function t() {
            null != e &&
              e &&
              !e.destroyed &&
              (void 0 !== e.imagesLoaded && (e.imagesLoaded += 1),
              e.imagesLoaded === e.imagesToLoad.length &&
                (e.params.updateOnImagesReady && e.update(),
                e.emit('imagesReady')))
          }
          e.imagesToLoad = e.$el.find('img')
          for (var i = 0; i < e.imagesToLoad.length; i += 1) {
            var s = e.imagesToLoad[i]
            e.loadImage(
              s,
              s.currentSrc || s.getAttribute('src'),
              s.srcset || s.getAttribute('srcset'),
              s.sizes || s.getAttribute('sizes'),
              !0,
              t
            )
          }
        },
      },
    },
    F = {},
    W = (function (e) {
      function t() {
        for (var i, a, r, l = [], d = arguments.length; d--; )
          l[d] = arguments[d]
        1 === l.length && l[0].constructor && l[0].constructor === Object
          ? (r = l[0])
          : ((a = (i = l)[0]), (r = i[1])),
          r || (r = {}),
          (r = n.extend({}, r)),
          a && !r.el && (r.el = a),
          e.call(this, r),
          Object.keys(Y).forEach(function (e) {
            Object.keys(Y[e]).forEach(function (i) {
              t.prototype[i] || (t.prototype[i] = Y[e][i])
            })
          })
        var h = this
        void 0 === h.modules && (h.modules = {}),
          Object.keys(h.modules).forEach(function (e) {
            var t = h.modules[e]
            if (t.params) {
              var i = Object.keys(t.params)[0],
                s = t.params[i]
              if ('object' != typeof s || null === s) return
              if (!(i in r && 'enabled' in s)) return
              !0 === r[i] && (r[i] = { enabled: !0 }),
                'object' != typeof r[i] ||
                  'enabled' in r[i] ||
                  (r[i].enabled = !0),
                r[i] || (r[i] = { enabled: !1 })
            }
          })
        var p = n.extend({}, V)
        h.useModulesParams(p),
          (h.params = n.extend({}, p, F, r)),
          (h.originalParams = n.extend({}, h.params)),
          (h.passedParams = n.extend({}, r)),
          (h.$ = s)
        var c = s(h.params.el)
        if ((a = c[0])) {
          if (c.length > 1) {
            var u = []
            return (
              c.each(function (e, i) {
                var s = n.extend({}, r, { el: i })
                u.push(new t(s))
              }),
              u
            )
          }
          var v, f, m
          return (
            (a.swiper = h),
            c.data('swiper', h),
            a && a.shadowRoot && a.shadowRoot.querySelector
              ? ((v = s(
                  a.shadowRoot.querySelector('.' + h.params.wrapperClass)
                )).children = function (e) {
                  return c.children(e)
                })
              : (v = c.children('.' + h.params.wrapperClass)),
            n.extend(h, {
              $el: c,
              el: a,
              $wrapperEl: v,
              wrapperEl: v[0],
              classNames: [],
              slides: s(),
              slidesGrid: [],
              snapGrid: [],
              slidesSizesGrid: [],
              isHorizontal: function () {
                return 'horizontal' === h.params.direction
              },
              isVertical: function () {
                return 'vertical' === h.params.direction
              },
              rtl:
                'rtl' === a.dir.toLowerCase() || 'rtl' === c.css('direction'),
              rtlTranslate:
                'horizontal' === h.params.direction &&
                ('rtl' === a.dir.toLowerCase() || 'rtl' === c.css('direction')),
              wrongRTL: '-webkit-box' === v.css('display'),
              activeIndex: 0,
              realIndex: 0,
              isBeginning: !0,
              isEnd: !1,
              translate: 0,
              previousTranslate: 0,
              progress: 0,
              velocity: 0,
              animating: !1,
              allowSlideNext: h.params.allowSlideNext,
              allowSlidePrev: h.params.allowSlidePrev,
              touchEvents:
                ((f = ['touchstart', 'touchmove', 'touchend', 'touchcancel']),
                (m = ['mousedown', 'mousemove', 'mouseup']),
                o.pointerEvents &&
                  (m = ['pointerdown', 'pointermove', 'pointerup']),
                (h.touchEventsTouch = {
                  start: f[0],
                  move: f[1],
                  end: f[2],
                  cancel: f[3],
                }),
                (h.touchEventsDesktop = { start: m[0], move: m[1], end: m[2] }),
                o.touch || !h.params.simulateTouch
                  ? h.touchEventsTouch
                  : h.touchEventsDesktop),
              touchEventsData: {
                isTouched: void 0,
                isMoved: void 0,
                allowTouchCallbacks: void 0,
                touchStartTime: void 0,
                isScrolling: void 0,
                currentTranslate: void 0,
                startTranslate: void 0,
                allowThresholdMove: void 0,
                formElements: 'input, select, option, textarea, button, video',
                lastClickTime: n.now(),
                clickTimeout: void 0,
                velocities: [],
                allowMomentumBounce: void 0,
                isTouchEvent: void 0,
                startMoving: void 0,
              },
              allowClick: !0,
              allowTouchMove: h.params.allowTouchMove,
              touches: {
                startX: 0,
                startY: 0,
                currentX: 0,
                currentY: 0,
                diff: 0,
              },
              imagesToLoad: [],
              imagesLoaded: 0,
            }),
            h.useModules(),
            h.params.init && h.init(),
            h
          )
        }
      }
      e && (t.__proto__ = e),
        (t.prototype = Object.create(e && e.prototype)),
        (t.prototype.constructor = t)
      var i = {
        extendedDefaults: { configurable: !0 },
        defaults: { configurable: !0 },
        Class: { configurable: !0 },
        $: { configurable: !0 },
      }
      return (
        (t.prototype.slidesPerViewDynamic = function () {
          var e = this.params,
            t = this.slides,
            i = this.slidesGrid,
            s = this.size,
            a = this.activeIndex,
            r = 1
          if (e.centeredSlides) {
            for (
              var n, o = t[a].swiperSlideSize, l = a + 1;
              l < t.length;
              l += 1
            )
              t[l] &&
                !n &&
                ((r += 1), (o += t[l].swiperSlideSize) > s && (n = !0))
            for (var d = a - 1; d >= 0; d -= 1)
              t[d] &&
                !n &&
                ((r += 1), (o += t[d].swiperSlideSize) > s && (n = !0))
          } else
            for (var h = a + 1; h < t.length; h += 1)
              i[h] - i[a] < s && (r += 1)
          return r
        }),
        (t.prototype.update = function () {
          var e = this
          if (e && !e.destroyed) {
            var t = e.snapGrid,
              i = e.params
            i.breakpoints && e.setBreakpoint(),
              e.updateSize(),
              e.updateSlides(),
              e.updateProgress(),
              e.updateSlidesClasses(),
              e.params.freeMode
                ? (s(), e.params.autoHeight && e.updateAutoHeight())
                : (('auto' === e.params.slidesPerView ||
                    e.params.slidesPerView > 1) &&
                  e.isEnd &&
                  !e.params.centeredSlides
                    ? e.slideTo(e.slides.length - 1, 0, !1, !0)
                    : e.slideTo(e.activeIndex, 0, !1, !0)) || s(),
              i.watchOverflow && t !== e.snapGrid && e.checkOverflow(),
              e.emit('update')
          }
          function s() {
            var t = e.rtlTranslate ? -1 * e.translate : e.translate,
              i = Math.min(Math.max(t, e.maxTranslate()), e.minTranslate())
            e.setTranslate(i), e.updateActiveIndex(), e.updateSlidesClasses()
          }
        }),
        (t.prototype.changeDirection = function (e, t) {
          void 0 === t && (t = !0)
          var i = this.params.direction
          return (
            e || (e = 'horizontal' === i ? 'vertical' : 'horizontal'),
            e === i || ('horizontal' !== e && 'vertical' !== e)
              ? this
              : (this.$el
                  .removeClass('' + this.params.containerModifierClass + i)
                  .addClass('' + this.params.containerModifierClass + e),
                (this.params.direction = e),
                this.slides.each(function (t, i) {
                  'vertical' === e
                    ? (i.style.width = '')
                    : (i.style.height = '')
                }),
                this.emit('changeDirection'),
                t && this.update(),
                this)
          )
        }),
        (t.prototype.init = function () {
          this.initialized ||
            (this.emit('beforeInit'),
            this.params.breakpoints && this.setBreakpoint(),
            this.addClasses(),
            this.params.loop && this.loopCreate(),
            this.updateSize(),
            this.updateSlides(),
            this.params.watchOverflow && this.checkOverflow(),
            this.params.grabCursor && this.setGrabCursor(),
            this.params.preloadImages && this.preloadImages(),
            this.params.loop
              ? this.slideTo(
                  this.params.initialSlide + this.loopedSlides,
                  0,
                  this.params.runCallbacksOnInit
                )
              : this.slideTo(
                  this.params.initialSlide,
                  0,
                  this.params.runCallbacksOnInit
                ),
            this.attachEvents(),
            (this.initialized = !0),
            this.emit('init'))
        }),
        (t.prototype.destroy = function (e, t) {
          void 0 === e && (e = !0), void 0 === t && (t = !0)
          var i = this,
            s = i.params,
            a = i.$el,
            r = i.$wrapperEl,
            o = i.slides
          return void 0 === i.params || i.destroyed
            ? null
            : (i.emit('beforeDestroy'),
              (i.initialized = !1),
              i.detachEvents(),
              s.loop && i.loopDestroy(),
              t &&
                (i.removeClasses(),
                a.removeAttr('style'),
                r.removeAttr('style'),
                o &&
                  o.length &&
                  o
                    .removeClass(
                      [
                        s.slideVisibleClass,
                        s.slideActiveClass,
                        s.slideNextClass,
                        s.slidePrevClass,
                      ].join(' ')
                    )
                    .removeAttr('style')
                    .removeAttr('data-swiper-slide-index')),
              i.emit('destroy'),
              Object.keys(i.eventsListeners).forEach(function (e) {
                i.off(e)
              }),
              !1 !== e &&
                ((i.$el[0].swiper = null),
                i.$el.data('swiper', null),
                n.deleteProps(i)),
              (i.destroyed = !0),
              null)
        }),
        (t.extendDefaults = function (e) {
          n.extend(F, e)
        }),
        (i.extendedDefaults.get = function () {
          return F
        }),
        (i.defaults.get = function () {
          return V
        }),
        (i.Class.get = function () {
          return e
        }),
        (i.$.get = function () {
          return s
        }),
        Object.defineProperties(t, i),
        t
      )
    })(l),
    R = { name: 'device', proto: { device: I }, static: { device: I } },
    q = { name: 'support', proto: { support: o }, static: { support: o } },
    j = {
      isEdge: !!t.navigator.userAgent.match(/Edge/g),
      isSafari: (function () {
        var e = t.navigator.userAgent.toLowerCase()
        return (
          e.indexOf('safari') >= 0 &&
          e.indexOf('chrome') < 0 &&
          e.indexOf('android') < 0
        )
      })(),
      isUiWebView: /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(
        t.navigator.userAgent
      ),
    },
    K = { name: 'browser', proto: { browser: j }, static: { browser: j } },
    U = {
      name: 'resize',
      create: function () {
        var e = this
        n.extend(e, {
          resize: {
            resizeHandler: function () {
              e &&
                !e.destroyed &&
                e.initialized &&
                (e.emit('beforeResize'), e.emit('resize'))
            },
            orientationChangeHandler: function () {
              e && !e.destroyed && e.initialized && e.emit('orientationchange')
            },
          },
        })
      },
      on: {
        init: function () {
          t.addEventListener('resize', this.resize.resizeHandler),
            t.addEventListener(
              'orientationchange',
              this.resize.orientationChangeHandler
            )
        },
        destroy: function () {
          t.removeEventListener('resize', this.resize.resizeHandler),
            t.removeEventListener(
              'orientationchange',
              this.resize.orientationChangeHandler
            )
        },
      },
    },
    _ = {
      func: t.MutationObserver || t.WebkitMutationObserver,
      attach: function (e, i) {
        void 0 === i && (i = {})
        var s = this,
          a = new (0, _.func)(function (e) {
            if (1 !== e.length) {
              var i = function () {
                s.emit('observerUpdate', e[0])
              }
              t.requestAnimationFrame
                ? t.requestAnimationFrame(i)
                : t.setTimeout(i, 0)
            } else s.emit('observerUpdate', e[0])
          })
        a.observe(e, {
          attributes: void 0 === i.attributes || i.attributes,
          childList: void 0 === i.childList || i.childList,
          characterData: void 0 === i.characterData || i.characterData,
        }),
          s.observer.observers.push(a)
      },
      init: function () {
        if (o.observer && this.params.observer) {
          if (this.params.observeParents)
            for (var e = this.$el.parents(), t = 0; t < e.length; t += 1)
              this.observer.attach(e[t])
          this.observer.attach(this.$el[0], {
            childList: this.params.observeSlideChildren,
          }),
            this.observer.attach(this.$wrapperEl[0], { attributes: !1 })
        }
      },
      destroy: function () {
        this.observer.observers.forEach(function (e) {
          e.disconnect()
        }),
          (this.observer.observers = [])
      },
    },
    Z = {
      name: 'observer',
      params: { observer: !1, observeParents: !1, observeSlideChildren: !1 },
      create: function () {
        n.extend(this, {
          observer: {
            init: _.init.bind(this),
            attach: _.attach.bind(this),
            destroy: _.destroy.bind(this),
            observers: [],
          },
        })
      },
      on: {
        init: function () {
          this.observer.init()
        },
        destroy: function () {
          this.observer.destroy()
        },
      },
    },
    Q = {
      update: function (e) {
        var t = this,
          i = t.params,
          s = i.slidesPerView,
          a = i.slidesPerGroup,
          r = i.centeredSlides,
          o = t.params.virtual,
          l = o.addSlidesBefore,
          d = o.addSlidesAfter,
          h = t.virtual,
          p = h.from,
          c = h.to,
          u = h.slides,
          v = h.slidesGrid,
          f = h.renderSlide,
          m = h.offset
        t.updateActiveIndex()
        var g,
          b,
          w,
          y = t.activeIndex || 0
        ;(g = t.rtlTranslate ? 'right' : t.isHorizontal() ? 'left' : 'top'),
          r
            ? ((b = Math.floor(s / 2) + a + l), (w = Math.floor(s / 2) + a + d))
            : ((b = s + (a - 1) + l), (w = a + d))
        var x = Math.max((y || 0) - w, 0),
          T = Math.min((y || 0) + b, u.length - 1),
          E = (t.slidesGrid[x] || 0) - (t.slidesGrid[0] || 0)
        function S() {
          t.updateSlides(),
            t.updateProgress(),
            t.updateSlidesClasses(),
            t.lazy && t.params.lazy.enabled && t.lazy.load()
        }
        if (
          (n.extend(t.virtual, {
            from: x,
            to: T,
            offset: E,
            slidesGrid: t.slidesGrid,
          }),
          p === x && c === T && !e)
        )
          return (
            t.slidesGrid !== v && E !== m && t.slides.css(g, E + 'px'),
            void t.updateProgress()
          )
        if (t.params.virtual.renderExternal)
          return (
            t.params.virtual.renderExternal.call(t, {
              offset: E,
              from: x,
              to: T,
              slides: (function () {
                for (var e = [], t = x; t <= T; t += 1) e.push(u[t])
                return e
              })(),
            }),
            void S()
          )
        var C = [],
          M = []
        if (e) t.$wrapperEl.find('.' + t.params.slideClass).remove()
        else
          for (var P = p; P <= c; P += 1)
            (P < x || P > T) &&
              t.$wrapperEl
                .find(
                  '.' +
                    t.params.slideClass +
                    '[data-swiper-slide-index="' +
                    P +
                    '"]'
                )
                .remove()
        for (var z = 0; z < u.length; z += 1)
          z >= x &&
            z <= T &&
            (void 0 === c || e
              ? M.push(z)
              : (z > c && M.push(z), z < p && C.push(z)))
        M.forEach(function (e) {
          t.$wrapperEl.append(f(u[e], e))
        }),
          C.sort(function (e, t) {
            return t - e
          }).forEach(function (e) {
            t.$wrapperEl.prepend(f(u[e], e))
          }),
          t.$wrapperEl.children('.swiper-slide').css(g, E + 'px'),
          S()
      },
      renderSlide: function (e, t) {
        var i = this.params.virtual
        if (i.cache && this.virtual.cache[t]) return this.virtual.cache[t]
        var a = i.renderSlide
          ? s(i.renderSlide.call(this, e, t))
          : s(
              '<div class="' +
                this.params.slideClass +
                '" data-swiper-slide-index="' +
                t +
                '">' +
                e +
                '</div>'
            )
        return (
          a.attr('data-swiper-slide-index') ||
            a.attr('data-swiper-slide-index', t),
          i.cache && (this.virtual.cache[t] = a),
          a
        )
      },
      appendSlide: function (e) {
        if ('object' == typeof e && 'length' in e)
          for (var t = 0; t < e.length; t += 1)
            e[t] && this.virtual.slides.push(e[t])
        else this.virtual.slides.push(e)
        this.virtual.update(!0)
      },
      prependSlide: function (e) {
        var t = this.activeIndex,
          i = t + 1,
          s = 1
        if (Array.isArray(e)) {
          for (var a = 0; a < e.length; a += 1)
            e[a] && this.virtual.slides.unshift(e[a])
          ;(i = t + e.length), (s = e.length)
        } else this.virtual.slides.unshift(e)
        if (this.params.virtual.cache) {
          var r = this.virtual.cache,
            n = {}
          Object.keys(r).forEach(function (e) {
            var t = r[e],
              i = t.attr('data-swiper-slide-index')
            i && t.attr('data-swiper-slide-index', parseInt(i, 10) + 1),
              (n[parseInt(e, 10) + s] = t)
          }),
            (this.virtual.cache = n)
        }
        this.virtual.update(!0), this.slideTo(i, 0)
      },
      removeSlide: function (e) {
        if (null != e) {
          var t = this.activeIndex
          if (Array.isArray(e))
            for (var i = e.length - 1; i >= 0; i -= 1)
              this.virtual.slides.splice(e[i], 1),
                this.params.virtual.cache && delete this.virtual.cache[e[i]],
                e[i] < t && (t -= 1),
                (t = Math.max(t, 0))
          else
            this.virtual.slides.splice(e, 1),
              this.params.virtual.cache && delete this.virtual.cache[e],
              e < t && (t -= 1),
              (t = Math.max(t, 0))
          this.virtual.update(!0), this.slideTo(t, 0)
        }
      },
      removeAllSlides: function () {
        ;(this.virtual.slides = []),
          this.params.virtual.cache && (this.virtual.cache = {}),
          this.virtual.update(!0),
          this.slideTo(0, 0)
      },
    },
    J = {
      name: 'virtual',
      params: {
        virtual: {
          enabled: !1,
          slides: [],
          cache: !0,
          renderSlide: null,
          renderExternal: null,
          addSlidesBefore: 0,
          addSlidesAfter: 0,
        },
      },
      create: function () {
        n.extend(this, {
          virtual: {
            update: Q.update.bind(this),
            appendSlide: Q.appendSlide.bind(this),
            prependSlide: Q.prependSlide.bind(this),
            removeSlide: Q.removeSlide.bind(this),
            removeAllSlides: Q.removeAllSlides.bind(this),
            renderSlide: Q.renderSlide.bind(this),
            slides: this.params.virtual.slides,
            cache: {},
          },
        })
      },
      on: {
        beforeInit: function () {
          if (this.params.virtual.enabled) {
            this.classNames.push(this.params.containerModifierClass + 'virtual')
            var e = { watchSlidesProgress: !0 }
            n.extend(this.params, e),
              n.extend(this.originalParams, e),
              this.params.initialSlide || this.virtual.update()
          }
        },
        setTranslate: function () {
          this.params.virtual.enabled && this.virtual.update()
        },
      },
    },
    ee = {
      handle: function (i) {
        var s = this.rtlTranslate,
          a = i
        a.originalEvent && (a = a.originalEvent)
        var r = a.keyCode || a.charCode
        if (
          !this.allowSlideNext &&
          ((this.isHorizontal() && 39 === r) ||
            (this.isVertical() && 40 === r) ||
            34 === r)
        )
          return !1
        if (
          !this.allowSlidePrev &&
          ((this.isHorizontal() && 37 === r) ||
            (this.isVertical() && 38 === r) ||
            33 === r)
        )
          return !1
        if (
          !(
            a.shiftKey ||
            a.altKey ||
            a.ctrlKey ||
            a.metaKey ||
            (e.activeElement &&
              e.activeElement.nodeName &&
              ('input' === e.activeElement.nodeName.toLowerCase() ||
                'textarea' === e.activeElement.nodeName.toLowerCase()))
          )
        ) {
          if (
            this.params.keyboard.onlyInViewport &&
            (33 === r ||
              34 === r ||
              37 === r ||
              39 === r ||
              38 === r ||
              40 === r)
          ) {
            var n = !1
            if (
              this.$el.parents('.' + this.params.slideClass).length > 0 &&
              0 === this.$el.parents('.' + this.params.slideActiveClass).length
            )
              return
            var o = t.innerWidth,
              l = t.innerHeight,
              d = this.$el.offset()
            s && (d.left -= this.$el[0].scrollLeft)
            for (
              var h = [
                  [d.left, d.top],
                  [d.left + this.width, d.top],
                  [d.left, d.top + this.height],
                  [d.left + this.width, d.top + this.height],
                ],
                p = 0;
              p < h.length;
              p += 1
            ) {
              var c = h[p]
              c[0] >= 0 && c[0] <= o && c[1] >= 0 && c[1] <= l && (n = !0)
            }
            if (!n) return
          }
          this.isHorizontal()
            ? ((33 !== r && 34 !== r && 37 !== r && 39 !== r) ||
                (a.preventDefault ? a.preventDefault() : (a.returnValue = !1)),
              (((34 !== r && 39 !== r) || s) &&
                ((33 !== r && 37 !== r) || !s)) ||
                this.slideNext(),
              (((33 !== r && 37 !== r) || s) &&
                ((34 !== r && 39 !== r) || !s)) ||
                this.slidePrev())
            : ((33 !== r && 34 !== r && 38 !== r && 40 !== r) ||
                (a.preventDefault ? a.preventDefault() : (a.returnValue = !1)),
              (34 !== r && 40 !== r) || this.slideNext(),
              (33 !== r && 38 !== r) || this.slidePrev()),
            this.emit('keyPress', r)
        }
      },
      enable: function () {
        this.keyboard.enabled ||
          (s(e).on('keydown', this.keyboard.handle),
          (this.keyboard.enabled = !0))
      },
      disable: function () {
        this.keyboard.enabled &&
          (s(e).off('keydown', this.keyboard.handle),
          (this.keyboard.enabled = !1))
      },
    },
    te = {
      name: 'keyboard',
      params: { keyboard: { enabled: !1, onlyInViewport: !0 } },
      create: function () {
        n.extend(this, {
          keyboard: {
            enabled: !1,
            enable: ee.enable.bind(this),
            disable: ee.disable.bind(this),
            handle: ee.handle.bind(this),
          },
        })
      },
      on: {
        init: function () {
          this.params.keyboard.enabled && this.keyboard.enable()
        },
        destroy: function () {
          this.keyboard.enabled && this.keyboard.disable()
        },
      },
    }
  var ie = {
      lastScrollTime: n.now(),
      lastEventBeforeSnap: void 0,
      recentWheelEvents: [],
      event: function () {
        return t.navigator.userAgent.indexOf('firefox') > -1
          ? 'DOMMouseScroll'
          : (function () {
              var t = 'onwheel' in e
              if (!t) {
                var i = e.createElement('div')
                i.setAttribute('onwheel', 'return;'),
                  (t = 'function' == typeof i.onwheel)
              }
              return (
                !t &&
                  e.implementation &&
                  e.implementation.hasFeature &&
                  !0 !== e.implementation.hasFeature('', '') &&
                  (t = e.implementation.hasFeature('Events.wheel', '3.0')),
                t
              )
            })()
          ? 'wheel'
          : 'mousewheel'
      },
      normalize: function (e) {
        var t = 0,
          i = 0,
          s = 0,
          a = 0
        return (
          'detail' in e && (i = e.detail),
          'wheelDelta' in e && (i = -e.wheelDelta / 120),
          'wheelDeltaY' in e && (i = -e.wheelDeltaY / 120),
          'wheelDeltaX' in e && (t = -e.wheelDeltaX / 120),
          'axis' in e && e.axis === e.HORIZONTAL_AXIS && ((t = i), (i = 0)),
          (s = 10 * t),
          (a = 10 * i),
          'deltaY' in e && (a = e.deltaY),
          'deltaX' in e && (s = e.deltaX),
          e.shiftKey && !s && ((s = a), (a = 0)),
          (s || a) &&
            e.deltaMode &&
            (1 === e.deltaMode
              ? ((s *= 40), (a *= 40))
              : ((s *= 800), (a *= 800))),
          s && !t && (t = s < 1 ? -1 : 1),
          a && !i && (i = a < 1 ? -1 : 1),
          { spinX: t, spinY: i, pixelX: s, pixelY: a }
        )
      },
      handleMouseEnter: function () {
        this.mouseEntered = !0
      },
      handleMouseLeave: function () {
        this.mouseEntered = !1
      },
      handle: function (e) {
        var t = e,
          i = this,
          s = i.params.mousewheel
        if (
          (i.params.cssMode && t.preventDefault(),
          !i.mouseEntered && !s.releaseOnEdges)
        )
          return !0
        t.originalEvent && (t = t.originalEvent)
        var a = 0,
          r = i.rtlTranslate ? -1 : 1,
          o = ie.normalize(t)
        if (s.forceToAxis)
          if (i.isHorizontal()) {
            if (!(Math.abs(o.pixelX) > Math.abs(o.pixelY))) return !0
            a = o.pixelX * r
          } else {
            if (!(Math.abs(o.pixelY) > Math.abs(o.pixelX))) return !0
            a = o.pixelY
          }
        else
          a =
            Math.abs(o.pixelX) > Math.abs(o.pixelY) ? -o.pixelX * r : -o.pixelY
        if (0 === a) return !0
        if ((s.invert && (a = -a), i.params.freeMode)) {
          var l = {
              time: n.now(),
              delta: Math.abs(a),
              direction: Math.sign(a),
            },
            d = i.mousewheel.lastEventBeforeSnap,
            h =
              d &&
              l.time < d.time + 500 &&
              l.delta <= d.delta &&
              l.direction === d.direction
          if (!h) {
            ;(i.mousewheel.lastEventBeforeSnap = void 0),
              i.params.loop && i.loopFix()
            var p = i.getTranslate() + a * s.sensitivity,
              c = i.isBeginning,
              u = i.isEnd
            if (
              (p >= i.minTranslate() && (p = i.minTranslate()),
              p <= i.maxTranslate() && (p = i.maxTranslate()),
              i.setTransition(0),
              i.setTranslate(p),
              i.updateProgress(),
              i.updateActiveIndex(),
              i.updateSlidesClasses(),
              ((!c && i.isBeginning) || (!u && i.isEnd)) &&
                i.updateSlidesClasses(),
              i.params.freeModeSticky)
            ) {
              clearTimeout(i.mousewheel.timeout),
                (i.mousewheel.timeout = void 0)
              var v = i.mousewheel.recentWheelEvents
              v.length >= 15 && v.shift()
              var f = v.length ? v[v.length - 1] : void 0,
                m = v[0]
              if (
                (v.push(l),
                f && (l.delta > f.delta || l.direction !== f.direction))
              )
                v.splice(0)
              else if (
                v.length >= 15 &&
                l.time - m.time < 500 &&
                m.delta - l.delta >= 1 &&
                l.delta <= 6
              ) {
                var g = a > 0 ? 0.8 : 0.2
                ;(i.mousewheel.lastEventBeforeSnap = l),
                  v.splice(0),
                  (i.mousewheel.timeout = n.nextTick(function () {
                    i.slideToClosest(i.params.speed, !0, void 0, g)
                  }, 0))
              }
              i.mousewheel.timeout ||
                (i.mousewheel.timeout = n.nextTick(function () {
                  ;(i.mousewheel.lastEventBeforeSnap = l),
                    v.splice(0),
                    i.slideToClosest(i.params.speed, !0, void 0, 0.5)
                }, 500))
            }
            if (
              (h || i.emit('scroll', t),
              i.params.autoplay &&
                i.params.autoplayDisableOnInteraction &&
                i.autoplay.stop(),
              p === i.minTranslate() || p === i.maxTranslate())
            )
              return !0
          }
        } else {
          var b = {
              time: n.now(),
              delta: Math.abs(a),
              direction: Math.sign(a),
              raw: e,
            },
            w = i.mousewheel.recentWheelEvents
          w.length >= 2 && w.shift()
          var y = w.length ? w[w.length - 1] : void 0
          if (
            (w.push(b),
            y
              ? (b.direction !== y.direction || b.delta > y.delta) &&
                i.mousewheel.animateSlider(b)
              : i.mousewheel.animateSlider(b),
            i.mousewheel.releaseScroll(b))
          )
            return !0
        }
        return t.preventDefault ? t.preventDefault() : (t.returnValue = !1), !1
      },
      animateSlider: function (e) {
        return (
          (e.delta >= 6 && n.now() - this.mousewheel.lastScrollTime < 60) ||
          (e.direction < 0
            ? (this.isEnd && !this.params.loop) ||
              this.animating ||
              (this.slideNext(), this.emit('scroll', e.raw))
            : (this.isBeginning && !this.params.loop) ||
              this.animating ||
              (this.slidePrev(), this.emit('scroll', e.raw)),
          (this.mousewheel.lastScrollTime = new t.Date().getTime()),
          !1)
        )
      },
      releaseScroll: function (e) {
        var t = this.params.mousewheel
        if (e.direction < 0) {
          if (this.isEnd && !this.params.loop && t.releaseOnEdges) return !0
        } else if (this.isBeginning && !this.params.loop && t.releaseOnEdges)
          return !0
        return !1
      },
      enable: function () {
        var e = ie.event()
        if (this.params.cssMode)
          return (
            this.wrapperEl.removeEventListener(e, this.mousewheel.handle), !0
          )
        if (!e) return !1
        if (this.mousewheel.enabled) return !1
        var t = this.$el
        return (
          'container' !== this.params.mousewheel.eventsTarged &&
            (t = s(this.params.mousewheel.eventsTarged)),
          t.on('mouseenter', this.mousewheel.handleMouseEnter),
          t.on('mouseleave', this.mousewheel.handleMouseLeave),
          t.on(e, this.mousewheel.handle),
          (this.mousewheel.enabled = !0),
          !0
        )
      },
      disable: function () {
        var e = ie.event()
        if (this.params.cssMode)
          return this.wrapperEl.addEventListener(e, this.mousewheel.handle), !0
        if (!e) return !1
        if (!this.mousewheel.enabled) return !1
        var t = this.$el
        return (
          'container' !== this.params.mousewheel.eventsTarged &&
            (t = s(this.params.mousewheel.eventsTarged)),
          t.off(e, this.mousewheel.handle),
          (this.mousewheel.enabled = !1),
          !0
        )
      },
    },
    se = {
      update: function () {
        var e = this.params.navigation
        if (!this.params.loop) {
          var t = this.navigation,
            i = t.$nextEl,
            s = t.$prevEl
          s &&
            s.length > 0 &&
            (this.isBeginning
              ? s.addClass(e.disabledClass)
              : s.removeClass(e.disabledClass),
            s[
              this.params.watchOverflow && this.isLocked
                ? 'addClass'
                : 'removeClass'
            ](e.lockClass)),
            i &&
              i.length > 0 &&
              (this.isEnd
                ? i.addClass(e.disabledClass)
                : i.removeClass(e.disabledClass),
              i[
                this.params.watchOverflow && this.isLocked
                  ? 'addClass'
                  : 'removeClass'
              ](e.lockClass))
        }
      },
      onPrevClick: function (e) {
        e.preventDefault(),
          (this.isBeginning && !this.params.loop) || this.slidePrev()
      },
      onNextClick: function (e) {
        e.preventDefault(),
          (this.isEnd && !this.params.loop) || this.slideNext()
      },
      init: function () {
        var e,
          t,
          i = this.params.navigation
        ;(i.nextEl || i.prevEl) &&
          (i.nextEl &&
            ((e = s(i.nextEl)),
            this.params.uniqueNavElements &&
              'string' == typeof i.nextEl &&
              e.length > 1 &&
              1 === this.$el.find(i.nextEl).length &&
              (e = this.$el.find(i.nextEl))),
          i.prevEl &&
            ((t = s(i.prevEl)),
            this.params.uniqueNavElements &&
              'string' == typeof i.prevEl &&
              t.length > 1 &&
              1 === this.$el.find(i.prevEl).length &&
              (t = this.$el.find(i.prevEl))),
          e && e.length > 0 && e.on('click', this.navigation.onNextClick),
          t && t.length > 0 && t.on('click', this.navigation.onPrevClick),
          n.extend(this.navigation, {
            $nextEl: e,
            nextEl: e && e[0],
            $prevEl: t,
            prevEl: t && t[0],
          }))
      },
      destroy: function () {
        var e = this.navigation,
          t = e.$nextEl,
          i = e.$prevEl
        t &&
          t.length &&
          (t.off('click', this.navigation.onNextClick),
          t.removeClass(this.params.navigation.disabledClass)),
          i &&
            i.length &&
            (i.off('click', this.navigation.onPrevClick),
            i.removeClass(this.params.navigation.disabledClass))
      },
    },
    ae = {
      update: function () {
        var e = this.rtl,
          t = this.params.pagination
        if (
          t.el &&
          this.pagination.el &&
          this.pagination.$el &&
          0 !== this.pagination.$el.length
        ) {
          var i,
            a =
              this.virtual && this.params.virtual.enabled
                ? this.virtual.slides.length
                : this.slides.length,
            r = this.pagination.$el,
            n = this.params.loop
              ? Math.ceil(
                  (a - 2 * this.loopedSlides) / this.params.slidesPerGroup
                )
              : this.snapGrid.length
          if (
            (this.params.loop
              ? ((i = Math.ceil(
                  (this.activeIndex - this.loopedSlides) /
                    this.params.slidesPerGroup
                )) >
                  a - 1 - 2 * this.loopedSlides &&
                  (i -= a - 2 * this.loopedSlides),
                i > n - 1 && (i -= n),
                i < 0 &&
                  'bullets' !== this.params.paginationType &&
                  (i = n + i))
              : (i =
                  void 0 !== this.snapIndex
                    ? this.snapIndex
                    : this.activeIndex || 0),
            'bullets' === t.type &&
              this.pagination.bullets &&
              this.pagination.bullets.length > 0)
          ) {
            var o,
              l,
              d,
              h = this.pagination.bullets
            if (
              (t.dynamicBullets &&
                ((this.pagination.bulletSize = h
                  .eq(0)
                  [this.isHorizontal() ? 'outerWidth' : 'outerHeight'](!0)),
                r.css(
                  this.isHorizontal() ? 'width' : 'height',
                  this.pagination.bulletSize * (t.dynamicMainBullets + 4) + 'px'
                ),
                t.dynamicMainBullets > 1 &&
                  void 0 !== this.previousIndex &&
                  ((this.pagination.dynamicBulletIndex +=
                    i - this.previousIndex),
                  this.pagination.dynamicBulletIndex > t.dynamicMainBullets - 1
                    ? (this.pagination.dynamicBulletIndex =
                        t.dynamicMainBullets - 1)
                    : this.pagination.dynamicBulletIndex < 0 &&
                      (this.pagination.dynamicBulletIndex = 0)),
                (o = i - this.pagination.dynamicBulletIndex),
                (d =
                  ((l = o + (Math.min(h.length, t.dynamicMainBullets) - 1)) +
                    o) /
                  2)),
              h.removeClass(
                t.bulletActiveClass +
                  ' ' +
                  t.bulletActiveClass +
                  '-next ' +
                  t.bulletActiveClass +
                  '-next-next ' +
                  t.bulletActiveClass +
                  '-prev ' +
                  t.bulletActiveClass +
                  '-prev-prev ' +
                  t.bulletActiveClass +
                  '-main'
              ),
              r.length > 1)
            )
              h.each(function (e, a) {
                var r = s(a),
                  n = r.index()
                n === i && r.addClass(t.bulletActiveClass),
                  t.dynamicBullets &&
                    (n >= o &&
                      n <= l &&
                      r.addClass(t.bulletActiveClass + '-main'),
                    n === o &&
                      r
                        .prev()
                        .addClass(t.bulletActiveClass + '-prev')
                        .prev()
                        .addClass(t.bulletActiveClass + '-prev-prev'),
                    n === l &&
                      r
                        .next()
                        .addClass(t.bulletActiveClass + '-next')
                        .next()
                        .addClass(t.bulletActiveClass + '-next-next'))
              })
            else {
              var p = h.eq(i),
                c = p.index()
              if ((p.addClass(t.bulletActiveClass), t.dynamicBullets)) {
                for (var u = h.eq(o), v = h.eq(l), f = o; f <= l; f += 1)
                  h.eq(f).addClass(t.bulletActiveClass + '-main')
                if (this.params.loop)
                  if (c >= h.length - t.dynamicMainBullets) {
                    for (var m = t.dynamicMainBullets; m >= 0; m -= 1)
                      h.eq(h.length - m).addClass(t.bulletActiveClass + '-main')
                    h.eq(h.length - t.dynamicMainBullets - 1).addClass(
                      t.bulletActiveClass + '-prev'
                    )
                  } else
                    u
                      .prev()
                      .addClass(t.bulletActiveClass + '-prev')
                      .prev()
                      .addClass(t.bulletActiveClass + '-prev-prev'),
                      v
                        .next()
                        .addClass(t.bulletActiveClass + '-next')
                        .next()
                        .addClass(t.bulletActiveClass + '-next-next')
                else
                  u
                    .prev()
                    .addClass(t.bulletActiveClass + '-prev')
                    .prev()
                    .addClass(t.bulletActiveClass + '-prev-prev'),
                    v
                      .next()
                      .addClass(t.bulletActiveClass + '-next')
                      .next()
                      .addClass(t.bulletActiveClass + '-next-next')
              }
            }
            if (t.dynamicBullets) {
              var g = Math.min(h.length, t.dynamicMainBullets + 4),
                b =
                  (this.pagination.bulletSize * g -
                    this.pagination.bulletSize) /
                    2 -
                  d * this.pagination.bulletSize,
                w = e ? 'right' : 'left'
              h.css(this.isHorizontal() ? w : 'top', b + 'px')
            }
          }
          if (
            ('fraction' === t.type &&
              (r
                .find('.' + t.currentClass)
                .text(t.formatFractionCurrent(i + 1)),
              r.find('.' + t.totalClass).text(t.formatFractionTotal(n))),
            'progressbar' === t.type)
          ) {
            var y
            y = t.progressbarOpposite
              ? this.isHorizontal()
                ? 'vertical'
                : 'horizontal'
              : this.isHorizontal()
              ? 'horizontal'
              : 'vertical'
            var x = (i + 1) / n,
              T = 1,
              E = 1
            'horizontal' === y ? (T = x) : (E = x),
              r
                .find('.' + t.progressbarFillClass)
                .transform(
                  'translate3d(0,0,0) scaleX(' + T + ') scaleY(' + E + ')'
                )
                .transition(this.params.speed)
          }
          'custom' === t.type && t.renderCustom
            ? (r.html(t.renderCustom(this, i + 1, n)),
              this.emit('paginationRender', this, r[0]))
            : this.emit('paginationUpdate', this, r[0]),
            r[
              this.params.watchOverflow && this.isLocked
                ? 'addClass'
                : 'removeClass'
            ](t.lockClass)
        }
      },
      render: function () {
        var e = this.params.pagination
        if (
          e.el &&
          this.pagination.el &&
          this.pagination.$el &&
          0 !== this.pagination.$el.length
        ) {
          var t =
              this.virtual && this.params.virtual.enabled
                ? this.virtual.slides.length
                : this.slides.length,
            i = this.pagination.$el,
            s = ''
          if ('bullets' === e.type) {
            for (
              var a = this.params.loop
                  ? Math.ceil(
                      (t - 2 * this.loopedSlides) / this.params.slidesPerGroup
                    )
                  : this.snapGrid.length,
                r = 0;
              r < a;
              r += 1
            )
              e.renderBullet
                ? (s += e.renderBullet.call(this, r, e.bulletClass))
                : (s +=
                    '<' +
                    e.bulletElement +
                    ' class="' +
                    e.bulletClass +
                    '"></' +
                    e.bulletElement +
                    '>')
            i.html(s), (this.pagination.bullets = i.find('.' + e.bulletClass))
          }
          'fraction' === e.type &&
            ((s = e.renderFraction
              ? e.renderFraction.call(this, e.currentClass, e.totalClass)
              : '<span class="' +
                e.currentClass +
                '"></span> / <span class="' +
                e.totalClass +
                '"></span>'),
            i.html(s)),
            'progressbar' === e.type &&
              ((s = e.renderProgressbar
                ? e.renderProgressbar.call(this, e.progressbarFillClass)
                : '<span class="' + e.progressbarFillClass + '"></span>'),
              i.html(s)),
            'custom' !== e.type &&
              this.emit('paginationRender', this.pagination.$el[0])
        }
      },
      init: function () {
        var e = this,
          t = e.params.pagination
        if (t.el) {
          var i = s(t.el)
          0 !== i.length &&
            (e.params.uniqueNavElements &&
              'string' == typeof t.el &&
              i.length > 1 &&
              1 === e.$el.find(t.el).length &&
              (i = e.$el.find(t.el)),
            'bullets' === t.type && t.clickable && i.addClass(t.clickableClass),
            i.addClass(t.modifierClass + t.type),
            'bullets' === t.type &&
              t.dynamicBullets &&
              (i.addClass('' + t.modifierClass + t.type + '-dynamic'),
              (e.pagination.dynamicBulletIndex = 0),
              t.dynamicMainBullets < 1 && (t.dynamicMainBullets = 1)),
            'progressbar' === t.type &&
              t.progressbarOpposite &&
              i.addClass(t.progressbarOppositeClass),
            t.clickable &&
              i.on('click', '.' + t.bulletClass, function (t) {
                t.preventDefault()
                var i = s(this).index() * e.params.slidesPerGroup
                e.params.loop && (i += e.loopedSlides), e.slideTo(i)
              }),
            n.extend(e.pagination, { $el: i, el: i[0] }))
        }
      },
      destroy: function () {
        var e = this.params.pagination
        if (
          e.el &&
          this.pagination.el &&
          this.pagination.$el &&
          0 !== this.pagination.$el.length
        ) {
          var t = this.pagination.$el
          t.removeClass(e.hiddenClass),
            t.removeClass(e.modifierClass + e.type),
            this.pagination.bullets &&
              this.pagination.bullets.removeClass(e.bulletActiveClass),
            e.clickable && t.off('click', '.' + e.bulletClass)
        }
      },
    },
    re = {
      setTranslate: function () {
        if (this.params.scrollbar.el && this.scrollbar.el) {
          var e = this.scrollbar,
            t = this.rtlTranslate,
            i = this.progress,
            s = e.dragSize,
            a = e.trackSize,
            r = e.$dragEl,
            n = e.$el,
            o = this.params.scrollbar,
            l = s,
            d = (a - s) * i
          t
            ? (d = -d) > 0
              ? ((l = s - d), (d = 0))
              : -d + s > a && (l = a + d)
            : d < 0
            ? ((l = s + d), (d = 0))
            : d + s > a && (l = a - d),
            this.isHorizontal()
              ? (r.transform('translate3d(' + d + 'px, 0, 0)'),
                (r[0].style.width = l + 'px'))
              : (r.transform('translate3d(0px, ' + d + 'px, 0)'),
                (r[0].style.height = l + 'px')),
            o.hide &&
              (clearTimeout(this.scrollbar.timeout),
              (n[0].style.opacity = 1),
              (this.scrollbar.timeout = setTimeout(function () {
                ;(n[0].style.opacity = 0), n.transition(400)
              }, 1e3)))
        }
      },
      setTransition: function (e) {
        this.params.scrollbar.el &&
          this.scrollbar.el &&
          this.scrollbar.$dragEl.transition(e)
      },
      updateSize: function () {
        if (this.params.scrollbar.el && this.scrollbar.el) {
          var e = this.scrollbar,
            t = e.$dragEl,
            i = e.$el
          ;(t[0].style.width = ''), (t[0].style.height = '')
          var s,
            a = this.isHorizontal() ? i[0].offsetWidth : i[0].offsetHeight,
            r = this.size / this.virtualSize,
            o = r * (a / this.size)
          ;(s =
            'auto' === this.params.scrollbar.dragSize
              ? a * r
              : parseInt(this.params.scrollbar.dragSize, 10)),
            this.isHorizontal()
              ? (t[0].style.width = s + 'px')
              : (t[0].style.height = s + 'px'),
            (i[0].style.display = r >= 1 ? 'none' : ''),
            this.params.scrollbar.hide && (i[0].style.opacity = 0),
            n.extend(e, {
              trackSize: a,
              divider: r,
              moveDivider: o,
              dragSize: s,
            }),
            e.$el[
              this.params.watchOverflow && this.isLocked
                ? 'addClass'
                : 'removeClass'
            ](this.params.scrollbar.lockClass)
        }
      },
      getPointerPosition: function (e) {
        return this.isHorizontal()
          ? 'touchstart' === e.type || 'touchmove' === e.type
            ? e.targetTouches[0].clientX
            : e.clientX
          : 'touchstart' === e.type || 'touchmove' === e.type
          ? e.targetTouches[0].clientY
          : e.clientY
      },
      setDragPosition: function (e) {
        var t,
          i = this.scrollbar,
          s = this.rtlTranslate,
          a = i.$el,
          r = i.dragSize,
          n = i.trackSize,
          o = i.dragStartPos
        ;(t =
          (i.getPointerPosition(e) -
            a.offset()[this.isHorizontal() ? 'left' : 'top'] -
            (null !== o ? o : r / 2)) /
          (n - r)),
          (t = Math.max(Math.min(t, 1), 0)),
          s && (t = 1 - t)
        var l =
          this.minTranslate() + (this.maxTranslate() - this.minTranslate()) * t
        this.updateProgress(l),
          this.setTranslate(l),
          this.updateActiveIndex(),
          this.updateSlidesClasses()
      },
      onDragStart: function (e) {
        var t = this.params.scrollbar,
          i = this.scrollbar,
          s = this.$wrapperEl,
          a = i.$el,
          r = i.$dragEl
        ;(this.scrollbar.isTouched = !0),
          (this.scrollbar.dragStartPos =
            e.target === r[0] || e.target === r
              ? i.getPointerPosition(e) -
                e.target.getBoundingClientRect()[
                  this.isHorizontal() ? 'left' : 'top'
                ]
              : null),
          e.preventDefault(),
          e.stopPropagation(),
          s.transition(100),
          r.transition(100),
          i.setDragPosition(e),
          clearTimeout(this.scrollbar.dragTimeout),
          a.transition(0),
          t.hide && a.css('opacity', 1),
          this.params.cssMode &&
            this.$wrapperEl.css('scroll-snap-type', 'none'),
          this.emit('scrollbarDragStart', e)
      },
      onDragMove: function (e) {
        var t = this.scrollbar,
          i = this.$wrapperEl,
          s = t.$el,
          a = t.$dragEl
        this.scrollbar.isTouched &&
          (e.preventDefault ? e.preventDefault() : (e.returnValue = !1),
          t.setDragPosition(e),
          i.transition(0),
          s.transition(0),
          a.transition(0),
          this.emit('scrollbarDragMove', e))
      },
      onDragEnd: function (e) {
        var t = this.params.scrollbar,
          i = this.scrollbar,
          s = this.$wrapperEl,
          a = i.$el
        this.scrollbar.isTouched &&
          ((this.scrollbar.isTouched = !1),
          this.params.cssMode &&
            (this.$wrapperEl.css('scroll-snap-type', ''), s.transition('')),
          t.hide &&
            (clearTimeout(this.scrollbar.dragTimeout),
            (this.scrollbar.dragTimeout = n.nextTick(function () {
              a.css('opacity', 0), a.transition(400)
            }, 1e3))),
          this.emit('scrollbarDragEnd', e),
          t.snapOnRelease && this.slideToClosest())
      },
      enableDraggable: function () {
        if (this.params.scrollbar.el) {
          var t = this.scrollbar,
            i = this.touchEventsTouch,
            s = this.touchEventsDesktop,
            a = this.params,
            r = t.$el[0],
            n = !(!o.passiveListener || !a.passiveListeners) && {
              passive: !1,
              capture: !1,
            },
            l = !(!o.passiveListener || !a.passiveListeners) && {
              passive: !0,
              capture: !1,
            }
          o.touch
            ? (r.addEventListener(i.start, this.scrollbar.onDragStart, n),
              r.addEventListener(i.move, this.scrollbar.onDragMove, n),
              r.addEventListener(i.end, this.scrollbar.onDragEnd, l))
            : (r.addEventListener(s.start, this.scrollbar.onDragStart, n),
              e.addEventListener(s.move, this.scrollbar.onDragMove, n),
              e.addEventListener(s.end, this.scrollbar.onDragEnd, l))
        }
      },
      disableDraggable: function () {
        if (this.params.scrollbar.el) {
          var t = this.scrollbar,
            i = this.touchEventsTouch,
            s = this.touchEventsDesktop,
            a = this.params,
            r = t.$el[0],
            n = !(!o.passiveListener || !a.passiveListeners) && {
              passive: !1,
              capture: !1,
            },
            l = !(!o.passiveListener || !a.passiveListeners) && {
              passive: !0,
              capture: !1,
            }
          o.touch
            ? (r.removeEventListener(i.start, this.scrollbar.onDragStart, n),
              r.removeEventListener(i.move, this.scrollbar.onDragMove, n),
              r.removeEventListener(i.end, this.scrollbar.onDragEnd, l))
            : (r.removeEventListener(s.start, this.scrollbar.onDragStart, n),
              e.removeEventListener(s.move, this.scrollbar.onDragMove, n),
              e.removeEventListener(s.end, this.scrollbar.onDragEnd, l))
        }
      },
      init: function () {
        if (this.params.scrollbar.el) {
          var e = this.scrollbar,
            t = this.$el,
            i = this.params.scrollbar,
            a = s(i.el)
          this.params.uniqueNavElements &&
            'string' == typeof i.el &&
            a.length > 1 &&
            1 === t.find(i.el).length &&
            (a = t.find(i.el))
          var r = a.find('.' + this.params.scrollbar.dragClass)
          0 === r.length &&
            ((r = s(
              '<div class="' + this.params.scrollbar.dragClass + '"></div>'
            )),
            a.append(r)),
            n.extend(e, { $el: a, el: a[0], $dragEl: r, dragEl: r[0] }),
            i.draggable && e.enableDraggable()
        }
      },
      destroy: function () {
        this.scrollbar.disableDraggable()
      },
    },
    ne = {
      setTransform: function (e, t) {
        var i = this.rtl,
          a = s(e),
          r = i ? -1 : 1,
          n = a.attr('data-swiper-parallax') || '0',
          o = a.attr('data-swiper-parallax-x'),
          l = a.attr('data-swiper-parallax-y'),
          d = a.attr('data-swiper-parallax-scale'),
          h = a.attr('data-swiper-parallax-opacity')
        if (
          (o || l
            ? ((o = o || '0'), (l = l || '0'))
            : this.isHorizontal()
            ? ((o = n), (l = '0'))
            : ((l = n), (o = '0')),
          (o =
            o.indexOf('%') >= 0
              ? parseInt(o, 10) * t * r + '%'
              : o * t * r + 'px'),
          (l = l.indexOf('%') >= 0 ? parseInt(l, 10) * t + '%' : l * t + 'px'),
          null != h)
        ) {
          var p = h - (h - 1) * (1 - Math.abs(t))
          a[0].style.opacity = p
        }
        if (null == d) a.transform('translate3d(' + o + ', ' + l + ', 0px)')
        else {
          var c = d - (d - 1) * (1 - Math.abs(t))
          a.transform('translate3d(' + o + ', ' + l + ', 0px) scale(' + c + ')')
        }
      },
      setTranslate: function () {
        var e = this,
          t = e.$el,
          i = e.slides,
          a = e.progress,
          r = e.snapGrid
        t
          .children(
            '[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]'
          )
          .each(function (t, i) {
            e.parallax.setTransform(i, a)
          }),
          i.each(function (t, i) {
            var n = i.progress
            e.params.slidesPerGroup > 1 &&
              'auto' !== e.params.slidesPerView &&
              (n += Math.ceil(t / 2) - a * (r.length - 1)),
              (n = Math.min(Math.max(n, -1), 1)),
              s(i)
                .find(
                  '[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]'
                )
                .each(function (t, i) {
                  e.parallax.setTransform(i, n)
                })
          })
      },
      setTransition: function (e) {
        void 0 === e && (e = this.params.speed)
        this.$el
          .find(
            '[data-swiper-parallax], [data-swiper-parallax-x], [data-swiper-parallax-y], [data-swiper-parallax-opacity], [data-swiper-parallax-scale]'
          )
          .each(function (t, i) {
            var a = s(i),
              r = parseInt(a.attr('data-swiper-parallax-duration'), 10) || e
            0 === e && (r = 0), a.transition(r)
          })
      },
    },
    oe = {
      getDistanceBetweenTouches: function (e) {
        if (e.targetTouches.length < 2) return 1
        var t = e.targetTouches[0].pageX,
          i = e.targetTouches[0].pageY,
          s = e.targetTouches[1].pageX,
          a = e.targetTouches[1].pageY
        return Math.sqrt(Math.pow(s - t, 2) + Math.pow(a - i, 2))
      },
      onGestureStart: function (e) {
        var t = this.params.zoom,
          i = this.zoom,
          a = i.gesture
        if (
          ((i.fakeGestureTouched = !1), (i.fakeGestureMoved = !1), !o.gestures)
        ) {
          if (
            'touchstart' !== e.type ||
            ('touchstart' === e.type && e.targetTouches.length < 2)
          )
            return
          ;(i.fakeGestureTouched = !0),
            (a.scaleStart = oe.getDistanceBetweenTouches(e))
        }
        ;(a.$slideEl && a.$slideEl.length) ||
        ((a.$slideEl = s(e.target).closest('.swiper-slide')),
        0 === a.$slideEl.length &&
          (a.$slideEl = this.slides.eq(this.activeIndex)),
        (a.$imageEl = a.$slideEl.find('img, svg, canvas')),
        (a.$imageWrapEl = a.$imageEl.parent('.' + t.containerClass)),
        (a.maxRatio = a.$imageWrapEl.attr('data-swiper-zoom') || t.maxRatio),
        0 !== a.$imageWrapEl.length)
          ? (a.$imageEl.transition(0), (this.zoom.isScaling = !0))
          : (a.$imageEl = void 0)
      },
      onGestureChange: function (e) {
        var t = this.params.zoom,
          i = this.zoom,
          s = i.gesture
        if (!o.gestures) {
          if (
            'touchmove' !== e.type ||
            ('touchmove' === e.type && e.targetTouches.length < 2)
          )
            return
          ;(i.fakeGestureMoved = !0),
            (s.scaleMove = oe.getDistanceBetweenTouches(e))
        }
        s.$imageEl &&
          0 !== s.$imageEl.length &&
          (o.gestures
            ? (i.scale = e.scale * i.currentScale)
            : (i.scale = (s.scaleMove / s.scaleStart) * i.currentScale),
          i.scale > s.maxRatio &&
            (i.scale =
              s.maxRatio - 1 + Math.pow(i.scale - s.maxRatio + 1, 0.5)),
          i.scale < t.minRatio &&
            (i.scale =
              t.minRatio + 1 - Math.pow(t.minRatio - i.scale + 1, 0.5)),
          s.$imageEl.transform('translate3d(0,0,0) scale(' + i.scale + ')'))
      },
      onGestureEnd: function (e) {
        var t = this.params.zoom,
          i = this.zoom,
          s = i.gesture
        if (!o.gestures) {
          if (!i.fakeGestureTouched || !i.fakeGestureMoved) return
          if (
            'touchend' !== e.type ||
            ('touchend' === e.type && e.changedTouches.length < 2 && !I.android)
          )
            return
          ;(i.fakeGestureTouched = !1), (i.fakeGestureMoved = !1)
        }
        s.$imageEl &&
          0 !== s.$imageEl.length &&
          ((i.scale = Math.max(Math.min(i.scale, s.maxRatio), t.minRatio)),
          s.$imageEl
            .transition(this.params.speed)
            .transform('translate3d(0,0,0) scale(' + i.scale + ')'),
          (i.currentScale = i.scale),
          (i.isScaling = !1),
          1 === i.scale && (s.$slideEl = void 0))
      },
      onTouchStart: function (e) {
        var t = this.zoom,
          i = t.gesture,
          s = t.image
        i.$imageEl &&
          0 !== i.$imageEl.length &&
          (s.isTouched ||
            (I.android && e.preventDefault(),
            (s.isTouched = !0),
            (s.touchesStart.x =
              'touchstart' === e.type ? e.targetTouches[0].pageX : e.pageX),
            (s.touchesStart.y =
              'touchstart' === e.type ? e.targetTouches[0].pageY : e.pageY)))
      },
      onTouchMove: function (e) {
        var t = this.zoom,
          i = t.gesture,
          s = t.image,
          a = t.velocity
        if (
          i.$imageEl &&
          0 !== i.$imageEl.length &&
          ((this.allowClick = !1), s.isTouched && i.$slideEl)
        ) {
          s.isMoved ||
            ((s.width = i.$imageEl[0].offsetWidth),
            (s.height = i.$imageEl[0].offsetHeight),
            (s.startX = n.getTranslate(i.$imageWrapEl[0], 'x') || 0),
            (s.startY = n.getTranslate(i.$imageWrapEl[0], 'y') || 0),
            (i.slideWidth = i.$slideEl[0].offsetWidth),
            (i.slideHeight = i.$slideEl[0].offsetHeight),
            i.$imageWrapEl.transition(0),
            this.rtl && ((s.startX = -s.startX), (s.startY = -s.startY)))
          var r = s.width * t.scale,
            o = s.height * t.scale
          if (!(r < i.slideWidth && o < i.slideHeight)) {
            if (
              ((s.minX = Math.min(i.slideWidth / 2 - r / 2, 0)),
              (s.maxX = -s.minX),
              (s.minY = Math.min(i.slideHeight / 2 - o / 2, 0)),
              (s.maxY = -s.minY),
              (s.touchesCurrent.x =
                'touchmove' === e.type ? e.targetTouches[0].pageX : e.pageX),
              (s.touchesCurrent.y =
                'touchmove' === e.type ? e.targetTouches[0].pageY : e.pageY),
              !s.isMoved && !t.isScaling)
            ) {
              if (
                this.isHorizontal() &&
                ((Math.floor(s.minX) === Math.floor(s.startX) &&
                  s.touchesCurrent.x < s.touchesStart.x) ||
                  (Math.floor(s.maxX) === Math.floor(s.startX) &&
                    s.touchesCurrent.x > s.touchesStart.x))
              )
                return void (s.isTouched = !1)
              if (
                !this.isHorizontal() &&
                ((Math.floor(s.minY) === Math.floor(s.startY) &&
                  s.touchesCurrent.y < s.touchesStart.y) ||
                  (Math.floor(s.maxY) === Math.floor(s.startY) &&
                    s.touchesCurrent.y > s.touchesStart.y))
              )
                return void (s.isTouched = !1)
            }
            e.preventDefault(),
              e.stopPropagation(),
              (s.isMoved = !0),
              (s.currentX = s.touchesCurrent.x - s.touchesStart.x + s.startX),
              (s.currentY = s.touchesCurrent.y - s.touchesStart.y + s.startY),
              s.currentX < s.minX &&
                (s.currentX =
                  s.minX + 1 - Math.pow(s.minX - s.currentX + 1, 0.8)),
              s.currentX > s.maxX &&
                (s.currentX =
                  s.maxX - 1 + Math.pow(s.currentX - s.maxX + 1, 0.8)),
              s.currentY < s.minY &&
                (s.currentY =
                  s.minY + 1 - Math.pow(s.minY - s.currentY + 1, 0.8)),
              s.currentY > s.maxY &&
                (s.currentY =
                  s.maxY - 1 + Math.pow(s.currentY - s.maxY + 1, 0.8)),
              a.prevPositionX || (a.prevPositionX = s.touchesCurrent.x),
              a.prevPositionY || (a.prevPositionY = s.touchesCurrent.y),
              a.prevTime || (a.prevTime = Date.now()),
              (a.x =
                (s.touchesCurrent.x - a.prevPositionX) /
                (Date.now() - a.prevTime) /
                2),
              (a.y =
                (s.touchesCurrent.y - a.prevPositionY) /
                (Date.now() - a.prevTime) /
                2),
              Math.abs(s.touchesCurrent.x - a.prevPositionX) < 2 && (a.x = 0),
              Math.abs(s.touchesCurrent.y - a.prevPositionY) < 2 && (a.y = 0),
              (a.prevPositionX = s.touchesCurrent.x),
              (a.prevPositionY = s.touchesCurrent.y),
              (a.prevTime = Date.now()),
              i.$imageWrapEl.transform(
                'translate3d(' + s.currentX + 'px, ' + s.currentY + 'px,0)'
              )
          }
        }
      },
      onTouchEnd: function () {
        var e = this.zoom,
          t = e.gesture,
          i = e.image,
          s = e.velocity
        if (t.$imageEl && 0 !== t.$imageEl.length) {
          if (!i.isTouched || !i.isMoved)
            return (i.isTouched = !1), void (i.isMoved = !1)
          ;(i.isTouched = !1), (i.isMoved = !1)
          var a = 300,
            r = 300,
            n = s.x * a,
            o = i.currentX + n,
            l = s.y * r,
            d = i.currentY + l
          0 !== s.x && (a = Math.abs((o - i.currentX) / s.x)),
            0 !== s.y && (r = Math.abs((d - i.currentY) / s.y))
          var h = Math.max(a, r)
          ;(i.currentX = o), (i.currentY = d)
          var p = i.width * e.scale,
            c = i.height * e.scale
          ;(i.minX = Math.min(t.slideWidth / 2 - p / 2, 0)),
            (i.maxX = -i.minX),
            (i.minY = Math.min(t.slideHeight / 2 - c / 2, 0)),
            (i.maxY = -i.minY),
            (i.currentX = Math.max(Math.min(i.currentX, i.maxX), i.minX)),
            (i.currentY = Math.max(Math.min(i.currentY, i.maxY), i.minY)),
            t.$imageWrapEl
              .transition(h)
              .transform(
                'translate3d(' + i.currentX + 'px, ' + i.currentY + 'px,0)'
              )
        }
      },
      onTransitionEnd: function () {
        var e = this.zoom,
          t = e.gesture
        t.$slideEl &&
          this.previousIndex !== this.activeIndex &&
          (t.$imageEl.transform('translate3d(0,0,0) scale(1)'),
          t.$imageWrapEl.transform('translate3d(0,0,0)'),
          (e.scale = 1),
          (e.currentScale = 1),
          (t.$slideEl = void 0),
          (t.$imageEl = void 0),
          (t.$imageWrapEl = void 0))
      },
      toggle: function (e) {
        var t = this.zoom
        t.scale && 1 !== t.scale ? t.out() : t.in(e)
      },
      in: function (e) {
        var t,
          i,
          a,
          r,
          n,
          o,
          l,
          d,
          h,
          p,
          c,
          u,
          v,
          f,
          m,
          g,
          b = this.zoom,
          w = this.params.zoom,
          y = b.gesture,
          x = b.image
        ;(y.$slideEl ||
          ((y.$slideEl = this.clickedSlide
            ? s(this.clickedSlide)
            : this.slides.eq(this.activeIndex)),
          (y.$imageEl = y.$slideEl.find('img, svg, canvas')),
          (y.$imageWrapEl = y.$imageEl.parent('.' + w.containerClass))),
        y.$imageEl && 0 !== y.$imageEl.length) &&
          (y.$slideEl.addClass('' + w.zoomedSlideClass),
          void 0 === x.touchesStart.x && e
            ? ((t =
                'touchend' === e.type ? e.changedTouches[0].pageX : e.pageX),
              (i = 'touchend' === e.type ? e.changedTouches[0].pageY : e.pageY))
            : ((t = x.touchesStart.x), (i = x.touchesStart.y)),
          (b.scale = y.$imageWrapEl.attr('data-swiper-zoom') || w.maxRatio),
          (b.currentScale =
            y.$imageWrapEl.attr('data-swiper-zoom') || w.maxRatio),
          e
            ? ((m = y.$slideEl[0].offsetWidth),
              (g = y.$slideEl[0].offsetHeight),
              (a = y.$slideEl.offset().left + m / 2 - t),
              (r = y.$slideEl.offset().top + g / 2 - i),
              (l = y.$imageEl[0].offsetWidth),
              (d = y.$imageEl[0].offsetHeight),
              (h = l * b.scale),
              (p = d * b.scale),
              (v = -(c = Math.min(m / 2 - h / 2, 0))),
              (f = -(u = Math.min(g / 2 - p / 2, 0))),
              (n = a * b.scale) < c && (n = c),
              n > v && (n = v),
              (o = r * b.scale) < u && (o = u),
              o > f && (o = f))
            : ((n = 0), (o = 0)),
          y.$imageWrapEl
            .transition(300)
            .transform('translate3d(' + n + 'px, ' + o + 'px,0)'),
          y.$imageEl
            .transition(300)
            .transform('translate3d(0,0,0) scale(' + b.scale + ')'))
      },
      out: function () {
        var e = this.zoom,
          t = this.params.zoom,
          i = e.gesture
        i.$slideEl ||
          ((i.$slideEl = this.clickedSlide
            ? s(this.clickedSlide)
            : this.slides.eq(this.activeIndex)),
          (i.$imageEl = i.$slideEl.find('img, svg, canvas')),
          (i.$imageWrapEl = i.$imageEl.parent('.' + t.containerClass))),
          i.$imageEl &&
            0 !== i.$imageEl.length &&
            ((e.scale = 1),
            (e.currentScale = 1),
            i.$imageWrapEl.transition(300).transform('translate3d(0,0,0)'),
            i.$imageEl.transition(300).transform('translate3d(0,0,0) scale(1)'),
            i.$slideEl.removeClass('' + t.zoomedSlideClass),
            (i.$slideEl = void 0))
      },
      enable: function () {
        var e = this.zoom
        if (!e.enabled) {
          e.enabled = !0
          var t = !(
              'touchstart' !== this.touchEvents.start ||
              !o.passiveListener ||
              !this.params.passiveListeners
            ) && { passive: !0, capture: !1 },
            i = !o.passiveListener || { passive: !1, capture: !0 }
          o.gestures
            ? (this.$wrapperEl.on(
                'gesturestart',
                '.swiper-slide',
                e.onGestureStart,
                t
              ),
              this.$wrapperEl.on(
                'gesturechange',
                '.swiper-slide',
                e.onGestureChange,
                t
              ),
              this.$wrapperEl.on(
                'gestureend',
                '.swiper-slide',
                e.onGestureEnd,
                t
              ))
            : 'touchstart' === this.touchEvents.start &&
              (this.$wrapperEl.on(
                this.touchEvents.start,
                '.swiper-slide',
                e.onGestureStart,
                t
              ),
              this.$wrapperEl.on(
                this.touchEvents.move,
                '.swiper-slide',
                e.onGestureChange,
                i
              ),
              this.$wrapperEl.on(
                this.touchEvents.end,
                '.swiper-slide',
                e.onGestureEnd,
                t
              ),
              this.touchEvents.cancel &&
                this.$wrapperEl.on(
                  this.touchEvents.cancel,
                  '.swiper-slide',
                  e.onGestureEnd,
                  t
                )),
            this.$wrapperEl.on(
              this.touchEvents.move,
              '.' + this.params.zoom.containerClass,
              e.onTouchMove,
              i
            )
        }
      },
      disable: function () {
        var e = this.zoom
        if (e.enabled) {
          this.zoom.enabled = !1
          var t = !(
              'touchstart' !== this.touchEvents.start ||
              !o.passiveListener ||
              !this.params.passiveListeners
            ) && { passive: !0, capture: !1 },
            i = !o.passiveListener || { passive: !1, capture: !0 }
          o.gestures
            ? (this.$wrapperEl.off(
                'gesturestart',
                '.swiper-slide',
                e.onGestureStart,
                t
              ),
              this.$wrapperEl.off(
                'gesturechange',
                '.swiper-slide',
                e.onGestureChange,
                t
              ),
              this.$wrapperEl.off(
                'gestureend',
                '.swiper-slide',
                e.onGestureEnd,
                t
              ))
            : 'touchstart' === this.touchEvents.start &&
              (this.$wrapperEl.off(
                this.touchEvents.start,
                '.swiper-slide',
                e.onGestureStart,
                t
              ),
              this.$wrapperEl.off(
                this.touchEvents.move,
                '.swiper-slide',
                e.onGestureChange,
                i
              ),
              this.$wrapperEl.off(
                this.touchEvents.end,
                '.swiper-slide',
                e.onGestureEnd,
                t
              ),
              this.touchEvents.cancel &&
                this.$wrapperEl.off(
                  this.touchEvents.cancel,
                  '.swiper-slide',
                  e.onGestureEnd,
                  t
                )),
            this.$wrapperEl.off(
              this.touchEvents.move,
              '.' + this.params.zoom.containerClass,
              e.onTouchMove,
              i
            )
        }
      },
    },
    le = {
      loadInSlide: function (e, t) {
        void 0 === t && (t = !0)
        var i = this,
          a = i.params.lazy
        if (void 0 !== e && 0 !== i.slides.length) {
          var r =
              i.virtual && i.params.virtual.enabled
                ? i.$wrapperEl.children(
                    '.' +
                      i.params.slideClass +
                      '[data-swiper-slide-index="' +
                      e +
                      '"]'
                  )
                : i.slides.eq(e),
            n = r.find(
              '.' +
                a.elementClass +
                ':not(.' +
                a.loadedClass +
                '):not(.' +
                a.loadingClass +
                ')'
            )
          !r.hasClass(a.elementClass) ||
            r.hasClass(a.loadedClass) ||
            r.hasClass(a.loadingClass) ||
            (n = n.add(r[0])),
            0 !== n.length &&
              n.each(function (e, n) {
                var o = s(n)
                o.addClass(a.loadingClass)
                var l = o.attr('data-background'),
                  d = o.attr('data-src'),
                  h = o.attr('data-srcset'),
                  p = o.attr('data-sizes')
                i.loadImage(o[0], d || l, h, p, !1, function () {
                  if (null != i && i && (!i || i.params) && !i.destroyed) {
                    if (
                      (l
                        ? (o.css('background-image', 'url("' + l + '")'),
                          o.removeAttr('data-background'))
                        : (h &&
                            (o.attr('srcset', h), o.removeAttr('data-srcset')),
                          p && (o.attr('sizes', p), o.removeAttr('data-sizes')),
                          d && (o.attr('src', d), o.removeAttr('data-src'))),
                      o.addClass(a.loadedClass).removeClass(a.loadingClass),
                      r.find('.' + a.preloaderClass).remove(),
                      i.params.loop && t)
                    ) {
                      var e = r.attr('data-swiper-slide-index')
                      if (r.hasClass(i.params.slideDuplicateClass)) {
                        var s = i.$wrapperEl.children(
                          '[data-swiper-slide-index="' +
                            e +
                            '"]:not(.' +
                            i.params.slideDuplicateClass +
                            ')'
                        )
                        i.lazy.loadInSlide(s.index(), !1)
                      } else {
                        var n = i.$wrapperEl.children(
                          '.' +
                            i.params.slideDuplicateClass +
                            '[data-swiper-slide-index="' +
                            e +
                            '"]'
                        )
                        i.lazy.loadInSlide(n.index(), !1)
                      }
                    }
                    i.emit('lazyImageReady', r[0], o[0])
                  }
                }),
                  i.emit('lazyImageLoad', r[0], o[0])
              })
        }
      },
      load: function () {
        var e = this,
          t = e.$wrapperEl,
          i = e.params,
          a = e.slides,
          r = e.activeIndex,
          n = e.virtual && i.virtual.enabled,
          o = i.lazy,
          l = i.slidesPerView
        function d(e) {
          if (n) {
            if (
              t.children(
                '.' + i.slideClass + '[data-swiper-slide-index="' + e + '"]'
              ).length
            )
              return !0
          } else if (a[e]) return !0
          return !1
        }
        function h(e) {
          return n ? s(e).attr('data-swiper-slide-index') : s(e).index()
        }
        if (
          ('auto' === l && (l = 0),
          e.lazy.initialImageLoaded || (e.lazy.initialImageLoaded = !0),
          e.params.watchSlidesVisibility)
        )
          t.children('.' + i.slideVisibleClass).each(function (t, i) {
            var a = n ? s(i).attr('data-swiper-slide-index') : s(i).index()
            e.lazy.loadInSlide(a)
          })
        else if (l > 1)
          for (var p = r; p < r + l; p += 1) d(p) && e.lazy.loadInSlide(p)
        else e.lazy.loadInSlide(r)
        if (o.loadPrevNext)
          if (l > 1 || (o.loadPrevNextAmount && o.loadPrevNextAmount > 1)) {
            for (
              var c = o.loadPrevNextAmount,
                u = l,
                v = Math.min(r + u + Math.max(c, u), a.length),
                f = Math.max(r - Math.max(u, c), 0),
                m = r + l;
              m < v;
              m += 1
            )
              d(m) && e.lazy.loadInSlide(m)
            for (var g = f; g < r; g += 1) d(g) && e.lazy.loadInSlide(g)
          } else {
            var b = t.children('.' + i.slideNextClass)
            b.length > 0 && e.lazy.loadInSlide(h(b))
            var w = t.children('.' + i.slidePrevClass)
            w.length > 0 && e.lazy.loadInSlide(h(w))
          }
      },
    },
    de = {
      LinearSpline: function (e, t) {
        var i,
          s,
          a,
          r,
          n,
          o = function (e, t) {
            for (s = -1, i = e.length; i - s > 1; )
              e[(a = (i + s) >> 1)] <= t ? (s = a) : (i = a)
            return i
          }
        return (
          (this.x = e),
          (this.y = t),
          (this.lastIndex = e.length - 1),
          (this.interpolate = function (e) {
            return e
              ? ((n = o(this.x, e)),
                (r = n - 1),
                ((e - this.x[r]) * (this.y[n] - this.y[r])) /
                  (this.x[n] - this.x[r]) +
                  this.y[r])
              : 0
          }),
          this
        )
      },
      getInterpolateFunction: function (e) {
        this.controller.spline ||
          (this.controller.spline = this.params.loop
            ? new de.LinearSpline(this.slidesGrid, e.slidesGrid)
            : new de.LinearSpline(this.snapGrid, e.snapGrid))
      },
      setTranslate: function (e, t) {
        var i,
          s,
          a = this,
          r = a.controller.control
        function n(e) {
          var t = a.rtlTranslate ? -a.translate : a.translate
          'slide' === a.params.controller.by &&
            (a.controller.getInterpolateFunction(e),
            (s = -a.controller.spline.interpolate(-t))),
            (s && 'container' !== a.params.controller.by) ||
              ((i =
                (e.maxTranslate() - e.minTranslate()) /
                (a.maxTranslate() - a.minTranslate())),
              (s = (t - a.minTranslate()) * i + e.minTranslate())),
            a.params.controller.inverse && (s = e.maxTranslate() - s),
            e.updateProgress(s),
            e.setTranslate(s, a),
            e.updateActiveIndex(),
            e.updateSlidesClasses()
        }
        if (Array.isArray(r))
          for (var o = 0; o < r.length; o += 1)
            r[o] !== t && r[o] instanceof W && n(r[o])
        else r instanceof W && t !== r && n(r)
      },
      setTransition: function (e, t) {
        var i,
          s = this,
          a = s.controller.control
        function r(t) {
          t.setTransition(e, s),
            0 !== e &&
              (t.transitionStart(),
              t.params.autoHeight &&
                n.nextTick(function () {
                  t.updateAutoHeight()
                }),
              t.$wrapperEl.transitionEnd(function () {
                a &&
                  (t.params.loop &&
                    'slide' === s.params.controller.by &&
                    t.loopFix(),
                  t.transitionEnd())
              }))
        }
        if (Array.isArray(a))
          for (i = 0; i < a.length; i += 1)
            a[i] !== t && a[i] instanceof W && r(a[i])
        else a instanceof W && t !== a && r(a)
      },
    },
    he = {
      makeElFocusable: function (e) {
        return e.attr('tabIndex', '0'), e
      },
      addElRole: function (e, t) {
        return e.attr('role', t), e
      },
      addElLabel: function (e, t) {
        return e.attr('aria-label', t), e
      },
      disableEl: function (e) {
        return e.attr('aria-disabled', !0), e
      },
      enableEl: function (e) {
        return e.attr('aria-disabled', !1), e
      },
      onEnterKey: function (e) {
        var t = this.params.a11y
        if (13 === e.keyCode) {
          var i = s(e.target)
          this.navigation &&
            this.navigation.$nextEl &&
            i.is(this.navigation.$nextEl) &&
            ((this.isEnd && !this.params.loop) || this.slideNext(),
            this.isEnd
              ? this.a11y.notify(t.lastSlideMessage)
              : this.a11y.notify(t.nextSlideMessage)),
            this.navigation &&
              this.navigation.$prevEl &&
              i.is(this.navigation.$prevEl) &&
              ((this.isBeginning && !this.params.loop) || this.slidePrev(),
              this.isBeginning
                ? this.a11y.notify(t.firstSlideMessage)
                : this.a11y.notify(t.prevSlideMessage)),
            this.pagination &&
              i.is('.' + this.params.pagination.bulletClass) &&
              i[0].click()
        }
      },
      notify: function (e) {
        var t = this.a11y.liveRegion
        0 !== t.length && (t.html(''), t.html(e))
      },
      updateNavigation: function () {
        if (!this.params.loop && this.navigation) {
          var e = this.navigation,
            t = e.$nextEl,
            i = e.$prevEl
          i &&
            i.length > 0 &&
            (this.isBeginning ? this.a11y.disableEl(i) : this.a11y.enableEl(i)),
            t &&
              t.length > 0 &&
              (this.isEnd ? this.a11y.disableEl(t) : this.a11y.enableEl(t))
        }
      },
      updatePagination: function () {
        var e = this,
          t = e.params.a11y
        e.pagination &&
          e.params.pagination.clickable &&
          e.pagination.bullets &&
          e.pagination.bullets.length &&
          e.pagination.bullets.each(function (i, a) {
            var r = s(a)
            e.a11y.makeElFocusable(r),
              e.a11y.addElRole(r, 'button'),
              e.a11y.addElLabel(
                r,
                t.paginationBulletMessage.replace(/{{index}}/, r.index() + 1)
              )
          })
      },
      init: function () {
        this.$el.append(this.a11y.liveRegion)
        var e,
          t,
          i = this.params.a11y
        this.navigation &&
          this.navigation.$nextEl &&
          (e = this.navigation.$nextEl),
          this.navigation &&
            this.navigation.$prevEl &&
            (t = this.navigation.$prevEl),
          e &&
            (this.a11y.makeElFocusable(e),
            this.a11y.addElRole(e, 'button'),
            this.a11y.addElLabel(e, i.nextSlideMessage),
            e.on('keydown', this.a11y.onEnterKey)),
          t &&
            (this.a11y.makeElFocusable(t),
            this.a11y.addElRole(t, 'button'),
            this.a11y.addElLabel(t, i.prevSlideMessage),
            t.on('keydown', this.a11y.onEnterKey)),
          this.pagination &&
            this.params.pagination.clickable &&
            this.pagination.bullets &&
            this.pagination.bullets.length &&
            this.pagination.$el.on(
              'keydown',
              '.' + this.params.pagination.bulletClass,
              this.a11y.onEnterKey
            )
      },
      destroy: function () {
        var e, t
        this.a11y.liveRegion &&
          this.a11y.liveRegion.length > 0 &&
          this.a11y.liveRegion.remove(),
          this.navigation &&
            this.navigation.$nextEl &&
            (e = this.navigation.$nextEl),
          this.navigation &&
            this.navigation.$prevEl &&
            (t = this.navigation.$prevEl),
          e && e.off('keydown', this.a11y.onEnterKey),
          t && t.off('keydown', this.a11y.onEnterKey),
          this.pagination &&
            this.params.pagination.clickable &&
            this.pagination.bullets &&
            this.pagination.bullets.length &&
            this.pagination.$el.off(
              'keydown',
              '.' + this.params.pagination.bulletClass,
              this.a11y.onEnterKey
            )
      },
    },
    pe = {
      init: function () {
        if (this.params.history) {
          if (!t.history || !t.history.pushState)
            return (
              (this.params.history.enabled = !1),
              void (this.params.hashNavigation.enabled = !0)
            )
          var e = this.history
          ;(e.initialized = !0),
            (e.paths = pe.getPathValues()),
            (e.paths.key || e.paths.value) &&
              (e.scrollToSlide(
                0,
                e.paths.value,
                this.params.runCallbacksOnInit
              ),
              this.params.history.replaceState ||
                t.addEventListener('popstate', this.history.setHistoryPopState))
        }
      },
      destroy: function () {
        this.params.history.replaceState ||
          t.removeEventListener('popstate', this.history.setHistoryPopState)
      },
      setHistoryPopState: function () {
        ;(this.history.paths = pe.getPathValues()),
          this.history.scrollToSlide(
            this.params.speed,
            this.history.paths.value,
            !1
          )
      },
      getPathValues: function () {
        var e = t.location.pathname
            .slice(1)
            .split('/')
            .filter(function (e) {
              return '' !== e
            }),
          i = e.length
        return { key: e[i - 2], value: e[i - 1] }
      },
      setHistory: function (e, i) {
        if (this.history.initialized && this.params.history.enabled) {
          var s = this.slides.eq(i),
            a = pe.slugify(s.attr('data-history'))
          t.location.pathname.includes(e) || (a = e + '/' + a)
          var r = t.history.state
          ;(r && r.value === a) ||
            (this.params.history.replaceState
              ? t.history.replaceState({ value: a }, null, a)
              : t.history.pushState({ value: a }, null, a))
        }
      },
      slugify: function (e) {
        return e
          .toString()
          .replace(/\s+/g, '-')
          .replace(/[^\w-]+/g, '')
          .replace(/--+/g, '-')
          .replace(/^-+/, '')
          .replace(/-+$/, '')
      },
      scrollToSlide: function (e, t, i) {
        if (t)
          for (var s = 0, a = this.slides.length; s < a; s += 1) {
            var r = this.slides.eq(s)
            if (
              pe.slugify(r.attr('data-history')) === t &&
              !r.hasClass(this.params.slideDuplicateClass)
            ) {
              var n = r.index()
              this.slideTo(n, e, i)
            }
          }
        else this.slideTo(0, e, i)
      },
    },
    ce = {
      onHashCange: function () {
        var t = e.location.hash.replace('#', '')
        if (t !== this.slides.eq(this.activeIndex).attr('data-hash')) {
          var i = this.$wrapperEl
            .children('.' + this.params.slideClass + '[data-hash="' + t + '"]')
            .index()
          if (void 0 === i) return
          this.slideTo(i)
        }
      },
      setHash: function () {
        if (
          this.hashNavigation.initialized &&
          this.params.hashNavigation.enabled
        )
          if (
            this.params.hashNavigation.replaceState &&
            t.history &&
            t.history.replaceState
          )
            t.history.replaceState(
              null,
              null,
              '#' + this.slides.eq(this.activeIndex).attr('data-hash') || ''
            )
          else {
            var i = this.slides.eq(this.activeIndex),
              s = i.attr('data-hash') || i.attr('data-history')
            e.location.hash = s || ''
          }
      },
      init: function () {
        if (
          !(
            !this.params.hashNavigation.enabled ||
            (this.params.history && this.params.history.enabled)
          )
        ) {
          this.hashNavigation.initialized = !0
          var i = e.location.hash.replace('#', '')
          if (i)
            for (var a = 0, r = this.slides.length; a < r; a += 1) {
              var n = this.slides.eq(a)
              if (
                (n.attr('data-hash') || n.attr('data-history')) === i &&
                !n.hasClass(this.params.slideDuplicateClass)
              ) {
                var o = n.index()
                this.slideTo(o, 0, this.params.runCallbacksOnInit, !0)
              }
            }
          this.params.hashNavigation.watchState &&
            s(t).on('hashchange', this.hashNavigation.onHashCange)
        }
      },
      destroy: function () {
        this.params.hashNavigation.watchState &&
          s(t).off('hashchange', this.hashNavigation.onHashCange)
      },
    },
    ue = {
      run: function () {
        var e = this,
          t = e.slides.eq(e.activeIndex),
          i = e.params.autoplay.delay
        t.attr('data-swiper-autoplay') &&
          (i = t.attr('data-swiper-autoplay') || e.params.autoplay.delay),
          clearTimeout(e.autoplay.timeout),
          (e.autoplay.timeout = n.nextTick(function () {
            e.params.autoplay.reverseDirection
              ? e.params.loop
                ? (e.loopFix(),
                  e.slidePrev(e.params.speed, !0, !0),
                  e.emit('autoplay'))
                : e.isBeginning
                ? e.params.autoplay.stopOnLastSlide
                  ? e.autoplay.stop()
                  : (e.slideTo(e.slides.length - 1, e.params.speed, !0, !0),
                    e.emit('autoplay'))
                : (e.slidePrev(e.params.speed, !0, !0), e.emit('autoplay'))
              : e.params.loop
              ? (e.loopFix(),
                e.slideNext(e.params.speed, !0, !0),
                e.emit('autoplay'))
              : e.isEnd
              ? e.params.autoplay.stopOnLastSlide
                ? e.autoplay.stop()
                : (e.slideTo(0, e.params.speed, !0, !0), e.emit('autoplay'))
              : (e.slideNext(e.params.speed, !0, !0), e.emit('autoplay')),
              e.params.cssMode && e.autoplay.running && e.autoplay.run()
          }, i))
      },
      start: function () {
        return (
          void 0 === this.autoplay.timeout &&
          !this.autoplay.running &&
          ((this.autoplay.running = !0),
          this.emit('autoplayStart'),
          this.autoplay.run(),
          !0)
        )
      },
      stop: function () {
        return (
          !!this.autoplay.running &&
          void 0 !== this.autoplay.timeout &&
          (this.autoplay.timeout &&
            (clearTimeout(this.autoplay.timeout),
            (this.autoplay.timeout = void 0)),
          (this.autoplay.running = !1),
          this.emit('autoplayStop'),
          !0)
        )
      },
      pause: function (e) {
        this.autoplay.running &&
          (this.autoplay.paused ||
            (this.autoplay.timeout && clearTimeout(this.autoplay.timeout),
            (this.autoplay.paused = !0),
            0 !== e && this.params.autoplay.waitForTransition
              ? (this.$wrapperEl[0].addEventListener(
                  'transitionend',
                  this.autoplay.onTransitionEnd
                ),
                this.$wrapperEl[0].addEventListener(
                  'webkitTransitionEnd',
                  this.autoplay.onTransitionEnd
                ))
              : ((this.autoplay.paused = !1), this.autoplay.run())))
      },
    },
    ve = {
      setTranslate: function () {
        for (var e = this.slides, t = 0; t < e.length; t += 1) {
          var i = this.slides.eq(t),
            s = -i[0].swiperSlideOffset
          this.params.virtualTranslate || (s -= this.translate)
          var a = 0
          this.isHorizontal() || ((a = s), (s = 0))
          var r = this.params.fadeEffect.crossFade
            ? Math.max(1 - Math.abs(i[0].progress), 0)
            : 1 + Math.min(Math.max(i[0].progress, -1), 0)
          i.css({ opacity: r }).transform(
            'translate3d(' + s + 'px, ' + a + 'px, 0px)'
          )
        }
      },
      setTransition: function (e) {
        var t = this,
          i = t.slides,
          s = t.$wrapperEl
        if ((i.transition(e), t.params.virtualTranslate && 0 !== e)) {
          var a = !1
          i.transitionEnd(function () {
            if (!a && t && !t.destroyed) {
              ;(a = !0), (t.animating = !1)
              for (
                var e = ['webkitTransitionEnd', 'transitionend'], i = 0;
                i < e.length;
                i += 1
              )
                s.trigger(e[i])
            }
          })
        }
      },
    },
    fe = {
      setTranslate: function () {
        var e,
          t = this.$el,
          i = this.$wrapperEl,
          a = this.slides,
          r = this.width,
          n = this.height,
          o = this.rtlTranslate,
          l = this.size,
          d = this.params.cubeEffect,
          h = this.isHorizontal(),
          p = this.virtual && this.params.virtual.enabled,
          c = 0
        d.shadow &&
          (h
            ? (0 === (e = i.find('.swiper-cube-shadow')).length &&
                ((e = s('<div class="swiper-cube-shadow"></div>')),
                i.append(e)),
              e.css({ height: r + 'px' }))
            : 0 === (e = t.find('.swiper-cube-shadow')).length &&
              ((e = s('<div class="swiper-cube-shadow"></div>')), t.append(e)))
        for (var u = 0; u < a.length; u += 1) {
          var v = a.eq(u),
            f = u
          p && (f = parseInt(v.attr('data-swiper-slide-index'), 10))
          var m = 90 * f,
            g = Math.floor(m / 360)
          o && ((m = -m), (g = Math.floor(-m / 360)))
          var b = Math.max(Math.min(v[0].progress, 1), -1),
            w = 0,
            y = 0,
            x = 0
          f % 4 == 0
            ? ((w = 4 * -g * l), (x = 0))
            : (f - 1) % 4 == 0
            ? ((w = 0), (x = 4 * -g * l))
            : (f - 2) % 4 == 0
            ? ((w = l + 4 * g * l), (x = l))
            : (f - 3) % 4 == 0 && ((w = -l), (x = 3 * l + 4 * l * g)),
            o && (w = -w),
            h || ((y = w), (w = 0))
          var T =
            'rotateX(' +
            (h ? 0 : -m) +
            'deg) rotateY(' +
            (h ? m : 0) +
            'deg) translate3d(' +
            w +
            'px, ' +
            y +
            'px, ' +
            x +
            'px)'
          if (
            (b <= 1 &&
              b > -1 &&
              ((c = 90 * f + 90 * b), o && (c = 90 * -f - 90 * b)),
            v.transform(T),
            d.slideShadows)
          ) {
            var E = h
                ? v.find('.swiper-slide-shadow-left')
                : v.find('.swiper-slide-shadow-top'),
              S = h
                ? v.find('.swiper-slide-shadow-right')
                : v.find('.swiper-slide-shadow-bottom')
            0 === E.length &&
              ((E = s(
                '<div class="swiper-slide-shadow-' +
                  (h ? 'left' : 'top') +
                  '"></div>'
              )),
              v.append(E)),
              0 === S.length &&
                ((S = s(
                  '<div class="swiper-slide-shadow-' +
                    (h ? 'right' : 'bottom') +
                    '"></div>'
                )),
                v.append(S)),
              E.length && (E[0].style.opacity = Math.max(-b, 0)),
              S.length && (S[0].style.opacity = Math.max(b, 0))
          }
        }
        if (
          (i.css({
            '-webkit-transform-origin': '50% 50% -' + l / 2 + 'px',
            '-moz-transform-origin': '50% 50% -' + l / 2 + 'px',
            '-ms-transform-origin': '50% 50% -' + l / 2 + 'px',
            'transform-origin': '50% 50% -' + l / 2 + 'px',
          }),
          d.shadow)
        )
          if (h)
            e.transform(
              'translate3d(0px, ' +
                (r / 2 + d.shadowOffset) +
                'px, ' +
                -r / 2 +
                'px) rotateX(90deg) rotateZ(0deg) scale(' +
                d.shadowScale +
                ')'
            )
          else {
            var C = Math.abs(c) - 90 * Math.floor(Math.abs(c) / 90),
              M =
                1.5 -
                (Math.sin((2 * C * Math.PI) / 360) / 2 +
                  Math.cos((2 * C * Math.PI) / 360) / 2),
              P = d.shadowScale,
              z = d.shadowScale / M,
              k = d.shadowOffset
            e.transform(
              'scale3d(' +
                P +
                ', 1, ' +
                z +
                ') translate3d(0px, ' +
                (n / 2 + k) +
                'px, ' +
                -n / 2 / z +
                'px) rotateX(-90deg)'
            )
          }
        var $ = j.isSafari || j.isUiWebView ? -l / 2 : 0
        i.transform(
          'translate3d(0px,0,' +
            $ +
            'px) rotateX(' +
            (this.isHorizontal() ? 0 : c) +
            'deg) rotateY(' +
            (this.isHorizontal() ? -c : 0) +
            'deg)'
        )
      },
      setTransition: function (e) {
        var t = this.$el
        this.slides
          .transition(e)
          .find(
            '.swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left'
          )
          .transition(e),
          this.params.cubeEffect.shadow &&
            !this.isHorizontal() &&
            t.find('.swiper-cube-shadow').transition(e)
      },
    },
    me = {
      setTranslate: function () {
        for (
          var e = this.slides, t = this.rtlTranslate, i = 0;
          i < e.length;
          i += 1
        ) {
          var a = e.eq(i),
            r = a[0].progress
          this.params.flipEffect.limitRotation &&
            (r = Math.max(Math.min(a[0].progress, 1), -1))
          var n = -180 * r,
            o = 0,
            l = -a[0].swiperSlideOffset,
            d = 0
          if (
            (this.isHorizontal()
              ? t && (n = -n)
              : ((d = l), (l = 0), (o = -n), (n = 0)),
            (a[0].style.zIndex = -Math.abs(Math.round(r)) + e.length),
            this.params.flipEffect.slideShadows)
          ) {
            var h = this.isHorizontal()
                ? a.find('.swiper-slide-shadow-left')
                : a.find('.swiper-slide-shadow-top'),
              p = this.isHorizontal()
                ? a.find('.swiper-slide-shadow-right')
                : a.find('.swiper-slide-shadow-bottom')
            0 === h.length &&
              ((h = s(
                '<div class="swiper-slide-shadow-' +
                  (this.isHorizontal() ? 'left' : 'top') +
                  '"></div>'
              )),
              a.append(h)),
              0 === p.length &&
                ((p = s(
                  '<div class="swiper-slide-shadow-' +
                    (this.isHorizontal() ? 'right' : 'bottom') +
                    '"></div>'
                )),
                a.append(p)),
              h.length && (h[0].style.opacity = Math.max(-r, 0)),
              p.length && (p[0].style.opacity = Math.max(r, 0))
          }
          a.transform(
            'translate3d(' +
              l +
              'px, ' +
              d +
              'px, 0px) rotateX(' +
              o +
              'deg) rotateY(' +
              n +
              'deg)'
          )
        }
      },
      setTransition: function (e) {
        var t = this,
          i = t.slides,
          s = t.activeIndex,
          a = t.$wrapperEl
        if (
          (i
            .transition(e)
            .find(
              '.swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left'
            )
            .transition(e),
          t.params.virtualTranslate && 0 !== e)
        ) {
          var r = !1
          i.eq(s).transitionEnd(function () {
            if (!r && t && !t.destroyed) {
              ;(r = !0), (t.animating = !1)
              for (
                var e = ['webkitTransitionEnd', 'transitionend'], i = 0;
                i < e.length;
                i += 1
              )
                a.trigger(e[i])
            }
          })
        }
      },
    },
    ge = {
      setTranslate: function () {
        for (
          var e = this.width,
            t = this.height,
            i = this.slides,
            a = this.$wrapperEl,
            r = this.slidesSizesGrid,
            n = this.params.coverflowEffect,
            l = this.isHorizontal(),
            d = this.translate,
            h = l ? e / 2 - d : t / 2 - d,
            p = l ? n.rotate : -n.rotate,
            c = n.depth,
            u = 0,
            v = i.length;
          u < v;
          u += 1
        ) {
          var f = i.eq(u),
            m = r[u],
            g = ((h - f[0].swiperSlideOffset - m / 2) / m) * n.modifier,
            b = l ? p * g : 0,
            w = l ? 0 : p * g,
            y = -c * Math.abs(g),
            x = l ? 0 : n.stretch * g,
            T = l ? n.stretch * g : 0
          Math.abs(T) < 0.001 && (T = 0),
            Math.abs(x) < 0.001 && (x = 0),
            Math.abs(y) < 0.001 && (y = 0),
            Math.abs(b) < 0.001 && (b = 0),
            Math.abs(w) < 0.001 && (w = 0)
          var E =
            'translate3d(' +
            T +
            'px,' +
            x +
            'px,' +
            y +
            'px)  rotateX(' +
            w +
            'deg) rotateY(' +
            b +
            'deg)'
          if (
            (f.transform(E),
            (f[0].style.zIndex = 1 - Math.abs(Math.round(g))),
            n.slideShadows)
          ) {
            var S = l
                ? f.find('.swiper-slide-shadow-left')
                : f.find('.swiper-slide-shadow-top'),
              C = l
                ? f.find('.swiper-slide-shadow-right')
                : f.find('.swiper-slide-shadow-bottom')
            0 === S.length &&
              ((S = s(
                '<div class="swiper-slide-shadow-' +
                  (l ? 'left' : 'top') +
                  '"></div>'
              )),
              f.append(S)),
              0 === C.length &&
                ((C = s(
                  '<div class="swiper-slide-shadow-' +
                    (l ? 'right' : 'bottom') +
                    '"></div>'
                )),
                f.append(C)),
              S.length && (S[0].style.opacity = g > 0 ? g : 0),
              C.length && (C[0].style.opacity = -g > 0 ? -g : 0)
          }
        }
        ;(o.pointerEvents || o.prefixedPointerEvents) &&
          (a[0].style.perspectiveOrigin = h + 'px 50%')
      },
      setTransition: function (e) {
        this.slides
          .transition(e)
          .find(
            '.swiper-slide-shadow-top, .swiper-slide-shadow-right, .swiper-slide-shadow-bottom, .swiper-slide-shadow-left'
          )
          .transition(e)
      },
    },
    be = {
      init: function () {
        var e = this.params.thumbs,
          t = this.constructor
        e.swiper instanceof t
          ? ((this.thumbs.swiper = e.swiper),
            n.extend(this.thumbs.swiper.originalParams, {
              watchSlidesProgress: !0,
              slideToClickedSlide: !1,
            }),
            n.extend(this.thumbs.swiper.params, {
              watchSlidesProgress: !0,
              slideToClickedSlide: !1,
            }))
          : n.isObject(e.swiper) &&
            ((this.thumbs.swiper = new t(
              n.extend({}, e.swiper, {
                watchSlidesVisibility: !0,
                watchSlidesProgress: !0,
                slideToClickedSlide: !1,
              })
            )),
            (this.thumbs.swiperCreated = !0)),
          this.thumbs.swiper.$el.addClass(
            this.params.thumbs.thumbsContainerClass
          ),
          this.thumbs.swiper.on('tap', this.thumbs.onThumbClick)
      },
      onThumbClick: function () {
        var e = this.thumbs.swiper
        if (e) {
          var t = e.clickedIndex,
            i = e.clickedSlide
          if (
            !(
              (i && s(i).hasClass(this.params.thumbs.slideThumbActiveClass)) ||
              null == t
            )
          ) {
            var a
            if (
              ((a = e.params.loop
                ? parseInt(
                    s(e.clickedSlide).attr('data-swiper-slide-index'),
                    10
                  )
                : t),
              this.params.loop)
            ) {
              var r = this.activeIndex
              this.slides.eq(r).hasClass(this.params.slideDuplicateClass) &&
                (this.loopFix(),
                (this._clientLeft = this.$wrapperEl[0].clientLeft),
                (r = this.activeIndex))
              var n = this.slides
                  .eq(r)
                  .prevAll('[data-swiper-slide-index="' + a + '"]')
                  .eq(0)
                  .index(),
                o = this.slides
                  .eq(r)
                  .nextAll('[data-swiper-slide-index="' + a + '"]')
                  .eq(0)
                  .index()
              a = void 0 === n ? o : void 0 === o ? n : o - r < r - n ? o : n
            }
            this.slideTo(a)
          }
        }
      },
      update: function (e) {
        var t = this.thumbs.swiper
        if (t) {
          var i =
            'auto' === t.params.slidesPerView
              ? t.slidesPerViewDynamic()
              : t.params.slidesPerView
          if (this.realIndex !== t.realIndex) {
            var s,
              a = t.activeIndex
            if (t.params.loop) {
              t.slides.eq(a).hasClass(t.params.slideDuplicateClass) &&
                (t.loopFix(),
                (t._clientLeft = t.$wrapperEl[0].clientLeft),
                (a = t.activeIndex))
              var r = t.slides
                  .eq(a)
                  .prevAll('[data-swiper-slide-index="' + this.realIndex + '"]')
                  .eq(0)
                  .index(),
                n = t.slides
                  .eq(a)
                  .nextAll('[data-swiper-slide-index="' + this.realIndex + '"]')
                  .eq(0)
                  .index()
              s =
                void 0 === r
                  ? n
                  : void 0 === n
                  ? r
                  : n - a == a - r
                  ? a
                  : n - a < a - r
                  ? n
                  : r
            } else s = this.realIndex
            t.visibleSlidesIndexes &&
              t.visibleSlidesIndexes.indexOf(s) < 0 &&
              (t.params.centeredSlides
                ? (s =
                    s > a
                      ? s - Math.floor(i / 2) + 1
                      : s + Math.floor(i / 2) - 1)
                : s > a && (s = s - i + 1),
              t.slideTo(s, e ? 0 : void 0))
          }
          var o = 1,
            l = this.params.thumbs.slideThumbActiveClass
          if (
            (this.params.slidesPerView > 1 &&
              !this.params.centeredSlides &&
              (o = this.params.slidesPerView),
            this.params.thumbs.multipleActiveThumbs || (o = 1),
            (o = Math.floor(o)),
            t.slides.removeClass(l),
            t.params.loop || (t.params.virtual && t.params.virtual.enabled))
          )
            for (var d = 0; d < o; d += 1)
              t.$wrapperEl
                .children(
                  '[data-swiper-slide-index="' + (this.realIndex + d) + '"]'
                )
                .addClass(l)
          else
            for (var h = 0; h < o; h += 1)
              t.slides.eq(this.realIndex + h).addClass(l)
        }
      },
    },
    we = [
      R,
      q,
      K,
      U,
      Z,
      J,
      te,
      {
        name: 'mousewheel',
        params: {
          mousewheel: {
            enabled: !1,
            releaseOnEdges: !1,
            invert: !1,
            forceToAxis: !1,
            sensitivity: 1,
            eventsTarged: 'container',
          },
        },
        create: function () {
          n.extend(this, {
            mousewheel: {
              enabled: !1,
              enable: ie.enable.bind(this),
              disable: ie.disable.bind(this),
              handle: ie.handle.bind(this),
              handleMouseEnter: ie.handleMouseEnter.bind(this),
              handleMouseLeave: ie.handleMouseLeave.bind(this),
              animateSlider: ie.animateSlider.bind(this),
              releaseScroll: ie.releaseScroll.bind(this),
              lastScrollTime: n.now(),
              lastEventBeforeSnap: void 0,
              recentWheelEvents: [],
            },
          })
        },
        on: {
          init: function () {
            !this.params.mousewheel.enabled &&
              this.params.cssMode &&
              this.mousewheel.disable(),
              this.params.mousewheel.enabled && this.mousewheel.enable()
          },
          destroy: function () {
            this.params.cssMode && this.mousewheel.enable(),
              this.mousewheel.enabled && this.mousewheel.disable()
          },
        },
      },
      {
        name: 'navigation',
        params: {
          navigation: {
            nextEl: null,
            prevEl: null,
            hideOnClick: !1,
            disabledClass: 'swiper-button-disabled',
            hiddenClass: 'swiper-button-hidden',
            lockClass: 'swiper-button-lock',
          },
        },
        create: function () {
          n.extend(this, {
            navigation: {
              init: se.init.bind(this),
              update: se.update.bind(this),
              destroy: se.destroy.bind(this),
              onNextClick: se.onNextClick.bind(this),
              onPrevClick: se.onPrevClick.bind(this),
            },
          })
        },
        on: {
          init: function () {
            this.navigation.init(), this.navigation.update()
          },
          toEdge: function () {
            this.navigation.update()
          },
          fromEdge: function () {
            this.navigation.update()
          },
          destroy: function () {
            this.navigation.destroy()
          },
          click: function (e) {
            var t,
              i = this.navigation,
              a = i.$nextEl,
              r = i.$prevEl
            !this.params.navigation.hideOnClick ||
              s(e.target).is(r) ||
              s(e.target).is(a) ||
              (a
                ? (t = a.hasClass(this.params.navigation.hiddenClass))
                : r && (t = r.hasClass(this.params.navigation.hiddenClass)),
              !0 === t
                ? this.emit('navigationShow', this)
                : this.emit('navigationHide', this),
              a && a.toggleClass(this.params.navigation.hiddenClass),
              r && r.toggleClass(this.params.navigation.hiddenClass))
          },
        },
      },
      {
        name: 'pagination',
        params: {
          pagination: {
            el: null,
            bulletElement: 'span',
            clickable: !1,
            hideOnClick: !1,
            renderBullet: null,
            renderProgressbar: null,
            renderFraction: null,
            renderCustom: null,
            progressbarOpposite: !1,
            type: 'bullets',
            dynamicBullets: !1,
            dynamicMainBullets: 1,
            formatFractionCurrent: function (e) {
              return e
            },
            formatFractionTotal: function (e) {
              return e
            },
            bulletClass: 'swiper-pagination-bullet',
            bulletActiveClass: 'swiper-pagination-bullet-active',
            modifierClass: 'swiper-pagination-',
            currentClass: 'swiper-pagination-current',
            totalClass: 'swiper-pagination-total',
            hiddenClass: 'swiper-pagination-hidden',
            progressbarFillClass: 'swiper-pagination-progressbar-fill',
            progressbarOppositeClass: 'swiper-pagination-progressbar-opposite',
            clickableClass: 'swiper-pagination-clickable',
            lockClass: 'swiper-pagination-lock',
          },
        },
        create: function () {
          n.extend(this, {
            pagination: {
              init: ae.init.bind(this),
              render: ae.render.bind(this),
              update: ae.update.bind(this),
              destroy: ae.destroy.bind(this),
              dynamicBulletIndex: 0,
            },
          })
        },
        on: {
          init: function () {
            this.pagination.init(),
              this.pagination.render(),
              this.pagination.update()
          },
          activeIndexChange: function () {
            this.params.loop
              ? this.pagination.update()
              : void 0 === this.snapIndex && this.pagination.update()
          },
          snapIndexChange: function () {
            this.params.loop || this.pagination.update()
          },
          slidesLengthChange: function () {
            this.params.loop &&
              (this.pagination.render(), this.pagination.update())
          },
          snapGridLengthChange: function () {
            this.params.loop ||
              (this.pagination.render(), this.pagination.update())
          },
          destroy: function () {
            this.pagination.destroy()
          },
          click: function (e) {
            this.params.pagination.el &&
              this.params.pagination.hideOnClick &&
              this.pagination.$el.length > 0 &&
              !s(e.target).hasClass(this.params.pagination.bulletClass) &&
              (!0 ===
              this.pagination.$el.hasClass(this.params.pagination.hiddenClass)
                ? this.emit('paginationShow', this)
                : this.emit('paginationHide', this),
              this.pagination.$el.toggleClass(
                this.params.pagination.hiddenClass
              ))
          },
        },
      },
      {
        name: 'scrollbar',
        params: {
          scrollbar: {
            el: null,
            dragSize: 'auto',
            hide: !1,
            draggable: !1,
            snapOnRelease: !0,
            lockClass: 'swiper-scrollbar-lock',
            dragClass: 'swiper-scrollbar-drag',
          },
        },
        create: function () {
          n.extend(this, {
            scrollbar: {
              init: re.init.bind(this),
              destroy: re.destroy.bind(this),
              updateSize: re.updateSize.bind(this),
              setTranslate: re.setTranslate.bind(this),
              setTransition: re.setTransition.bind(this),
              enableDraggable: re.enableDraggable.bind(this),
              disableDraggable: re.disableDraggable.bind(this),
              setDragPosition: re.setDragPosition.bind(this),
              getPointerPosition: re.getPointerPosition.bind(this),
              onDragStart: re.onDragStart.bind(this),
              onDragMove: re.onDragMove.bind(this),
              onDragEnd: re.onDragEnd.bind(this),
              isTouched: !1,
              timeout: null,
              dragTimeout: null,
            },
          })
        },
        on: {
          init: function () {
            this.scrollbar.init(),
              this.scrollbar.updateSize(),
              this.scrollbar.setTranslate()
          },
          update: function () {
            this.scrollbar.updateSize()
          },
          resize: function () {
            this.scrollbar.updateSize()
          },
          observerUpdate: function () {
            this.scrollbar.updateSize()
          },
          setTranslate: function () {
            this.scrollbar.setTranslate()
          },
          setTransition: function (e) {
            this.scrollbar.setTransition(e)
          },
          destroy: function () {
            this.scrollbar.destroy()
          },
        },
      },
      {
        name: 'parallax',
        params: { parallax: { enabled: !1 } },
        create: function () {
          n.extend(this, {
            parallax: {
              setTransform: ne.setTransform.bind(this),
              setTranslate: ne.setTranslate.bind(this),
              setTransition: ne.setTransition.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            this.params.parallax.enabled &&
              ((this.params.watchSlidesProgress = !0),
              (this.originalParams.watchSlidesProgress = !0))
          },
          init: function () {
            this.params.parallax.enabled && this.parallax.setTranslate()
          },
          setTranslate: function () {
            this.params.parallax.enabled && this.parallax.setTranslate()
          },
          setTransition: function (e) {
            this.params.parallax.enabled && this.parallax.setTransition(e)
          },
        },
      },
      {
        name: 'zoom',
        params: {
          zoom: {
            enabled: !1,
            maxRatio: 3,
            minRatio: 1,
            toggle: !0,
            containerClass: 'swiper-zoom-container',
            zoomedSlideClass: 'swiper-slide-zoomed',
          },
        },
        create: function () {
          var e = this,
            t = {
              enabled: !1,
              scale: 1,
              currentScale: 1,
              isScaling: !1,
              gesture: {
                $slideEl: void 0,
                slideWidth: void 0,
                slideHeight: void 0,
                $imageEl: void 0,
                $imageWrapEl: void 0,
                maxRatio: 3,
              },
              image: {
                isTouched: void 0,
                isMoved: void 0,
                currentX: void 0,
                currentY: void 0,
                minX: void 0,
                minY: void 0,
                maxX: void 0,
                maxY: void 0,
                width: void 0,
                height: void 0,
                startX: void 0,
                startY: void 0,
                touchesStart: {},
                touchesCurrent: {},
              },
              velocity: {
                x: void 0,
                y: void 0,
                prevPositionX: void 0,
                prevPositionY: void 0,
                prevTime: void 0,
              },
            }
          'onGestureStart onGestureChange onGestureEnd onTouchStart onTouchMove onTouchEnd onTransitionEnd toggle enable disable in out'
            .split(' ')
            .forEach(function (i) {
              t[i] = oe[i].bind(e)
            }),
            n.extend(e, { zoom: t })
          var i = 1
          Object.defineProperty(e.zoom, 'scale', {
            get: function () {
              return i
            },
            set: function (t) {
              if (i !== t) {
                var s = e.zoom.gesture.$imageEl
                    ? e.zoom.gesture.$imageEl[0]
                    : void 0,
                  a = e.zoom.gesture.$slideEl
                    ? e.zoom.gesture.$slideEl[0]
                    : void 0
                e.emit('zoomChange', t, s, a)
              }
              i = t
            },
          })
        },
        on: {
          init: function () {
            this.params.zoom.enabled && this.zoom.enable()
          },
          destroy: function () {
            this.zoom.disable()
          },
          touchStart: function (e) {
            this.zoom.enabled && this.zoom.onTouchStart(e)
          },
          touchEnd: function (e) {
            this.zoom.enabled && this.zoom.onTouchEnd(e)
          },
          doubleTap: function (e) {
            this.params.zoom.enabled &&
              this.zoom.enabled &&
              this.params.zoom.toggle &&
              this.zoom.toggle(e)
          },
          transitionEnd: function () {
            this.zoom.enabled &&
              this.params.zoom.enabled &&
              this.zoom.onTransitionEnd()
          },
          slideChange: function () {
            this.zoom.enabled &&
              this.params.zoom.enabled &&
              this.params.cssMode &&
              this.zoom.onTransitionEnd()
          },
        },
      },
      {
        name: 'lazy',
        params: {
          lazy: {
            enabled: !1,
            loadPrevNext: !1,
            loadPrevNextAmount: 1,
            loadOnTransitionStart: !1,
            elementClass: 'swiper-lazy',
            loadingClass: 'swiper-lazy-loading',
            loadedClass: 'swiper-lazy-loaded',
            preloaderClass: 'swiper-lazy-preloader',
          },
        },
        create: function () {
          n.extend(this, {
            lazy: {
              initialImageLoaded: !1,
              load: le.load.bind(this),
              loadInSlide: le.loadInSlide.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            this.params.lazy.enabled &&
              this.params.preloadImages &&
              (this.params.preloadImages = !1)
          },
          init: function () {
            this.params.lazy.enabled &&
              !this.params.loop &&
              0 === this.params.initialSlide &&
              this.lazy.load()
          },
          scroll: function () {
            this.params.freeMode &&
              !this.params.freeModeSticky &&
              this.lazy.load()
          },
          resize: function () {
            this.params.lazy.enabled && this.lazy.load()
          },
          scrollbarDragMove: function () {
            this.params.lazy.enabled && this.lazy.load()
          },
          transitionStart: function () {
            this.params.lazy.enabled &&
              (this.params.lazy.loadOnTransitionStart ||
                (!this.params.lazy.loadOnTransitionStart &&
                  !this.lazy.initialImageLoaded)) &&
              this.lazy.load()
          },
          transitionEnd: function () {
            this.params.lazy.enabled &&
              !this.params.lazy.loadOnTransitionStart &&
              this.lazy.load()
          },
          slideChange: function () {
            this.params.lazy.enabled && this.params.cssMode && this.lazy.load()
          },
        },
      },
      {
        name: 'controller',
        params: { controller: { control: void 0, inverse: !1, by: 'slide' } },
        create: function () {
          n.extend(this, {
            controller: {
              control: this.params.controller.control,
              getInterpolateFunction: de.getInterpolateFunction.bind(this),
              setTranslate: de.setTranslate.bind(this),
              setTransition: de.setTransition.bind(this),
            },
          })
        },
        on: {
          update: function () {
            this.controller.control &&
              this.controller.spline &&
              ((this.controller.spline = void 0), delete this.controller.spline)
          },
          resize: function () {
            this.controller.control &&
              this.controller.spline &&
              ((this.controller.spline = void 0), delete this.controller.spline)
          },
          observerUpdate: function () {
            this.controller.control &&
              this.controller.spline &&
              ((this.controller.spline = void 0), delete this.controller.spline)
          },
          setTranslate: function (e, t) {
            this.controller.control && this.controller.setTranslate(e, t)
          },
          setTransition: function (e, t) {
            this.controller.control && this.controller.setTransition(e, t)
          },
        },
      },
      {
        name: 'a11y',
        params: {
          a11y: {
            enabled: !0,
            notificationClass: 'swiper-notification',
            prevSlideMessage: 'Previous slide',
            nextSlideMessage: 'Next slide',
            firstSlideMessage: 'This is the first slide',
            lastSlideMessage: 'This is the last slide',
            paginationBulletMessage: 'Go to slide {{index}}',
          },
        },
        create: function () {
          var e = this
          n.extend(e, {
            a11y: {
              liveRegion: s(
                '<span class="' +
                  e.params.a11y.notificationClass +
                  '" aria-live="assertive" aria-atomic="true"></span>'
              ),
            },
          }),
            Object.keys(he).forEach(function (t) {
              e.a11y[t] = he[t].bind(e)
            })
        },
        on: {
          init: function () {
            this.params.a11y.enabled &&
              (this.a11y.init(), this.a11y.updateNavigation())
          },
          toEdge: function () {
            this.params.a11y.enabled && this.a11y.updateNavigation()
          },
          fromEdge: function () {
            this.params.a11y.enabled && this.a11y.updateNavigation()
          },
          paginationUpdate: function () {
            this.params.a11y.enabled && this.a11y.updatePagination()
          },
          destroy: function () {
            this.params.a11y.enabled && this.a11y.destroy()
          },
        },
      },
      {
        name: 'history',
        params: { history: { enabled: !1, replaceState: !1, key: 'slides' } },
        create: function () {
          n.extend(this, {
            history: {
              init: pe.init.bind(this),
              setHistory: pe.setHistory.bind(this),
              setHistoryPopState: pe.setHistoryPopState.bind(this),
              scrollToSlide: pe.scrollToSlide.bind(this),
              destroy: pe.destroy.bind(this),
            },
          })
        },
        on: {
          init: function () {
            this.params.history.enabled && this.history.init()
          },
          destroy: function () {
            this.params.history.enabled && this.history.destroy()
          },
          transitionEnd: function () {
            this.history.initialized &&
              this.history.setHistory(this.params.history.key, this.activeIndex)
          },
          slideChange: function () {
            this.history.initialized &&
              this.params.cssMode &&
              this.history.setHistory(this.params.history.key, this.activeIndex)
          },
        },
      },
      {
        name: 'hash-navigation',
        params: {
          hashNavigation: { enabled: !1, replaceState: !1, watchState: !1 },
        },
        create: function () {
          n.extend(this, {
            hashNavigation: {
              initialized: !1,
              init: ce.init.bind(this),
              destroy: ce.destroy.bind(this),
              setHash: ce.setHash.bind(this),
              onHashCange: ce.onHashCange.bind(this),
            },
          })
        },
        on: {
          init: function () {
            this.params.hashNavigation.enabled && this.hashNavigation.init()
          },
          destroy: function () {
            this.params.hashNavigation.enabled && this.hashNavigation.destroy()
          },
          transitionEnd: function () {
            this.hashNavigation.initialized && this.hashNavigation.setHash()
          },
          slideChange: function () {
            this.hashNavigation.initialized &&
              this.params.cssMode &&
              this.hashNavigation.setHash()
          },
        },
      },
      {
        name: 'autoplay',
        params: {
          autoplay: {
            enabled: !1,
            delay: 3e3,
            waitForTransition: !0,
            disableOnInteraction: !0,
            stopOnLastSlide: !1,
            reverseDirection: !1,
          },
        },
        create: function () {
          var e = this
          n.extend(e, {
            autoplay: {
              running: !1,
              paused: !1,
              run: ue.run.bind(e),
              start: ue.start.bind(e),
              stop: ue.stop.bind(e),
              pause: ue.pause.bind(e),
              onVisibilityChange: function () {
                'hidden' === document.visibilityState &&
                  e.autoplay.running &&
                  e.autoplay.pause(),
                  'visible' === document.visibilityState &&
                    e.autoplay.paused &&
                    (e.autoplay.run(), (e.autoplay.paused = !1))
              },
              onTransitionEnd: function (t) {
                e &&
                  !e.destroyed &&
                  e.$wrapperEl &&
                  t.target === this &&
                  (e.$wrapperEl[0].removeEventListener(
                    'transitionend',
                    e.autoplay.onTransitionEnd
                  ),
                  e.$wrapperEl[0].removeEventListener(
                    'webkitTransitionEnd',
                    e.autoplay.onTransitionEnd
                  ),
                  (e.autoplay.paused = !1),
                  e.autoplay.running ? e.autoplay.run() : e.autoplay.stop())
              },
            },
          })
        },
        on: {
          init: function () {
            this.params.autoplay.enabled &&
              (this.autoplay.start(),
              document.addEventListener(
                'visibilitychange',
                this.autoplay.onVisibilityChange
              ))
          },
          beforeTransitionStart: function (e, t) {
            this.autoplay.running &&
              (t || !this.params.autoplay.disableOnInteraction
                ? this.autoplay.pause(e)
                : this.autoplay.stop())
          },
          sliderFirstMove: function () {
            this.autoplay.running &&
              (this.params.autoplay.disableOnInteraction
                ? this.autoplay.stop()
                : this.autoplay.pause())
          },
          touchEnd: function () {
            this.params.cssMode &&
              this.autoplay.paused &&
              !this.params.autoplay.disableOnInteraction &&
              this.autoplay.run()
          },
          destroy: function () {
            this.autoplay.running && this.autoplay.stop(),
              document.removeEventListener(
                'visibilitychange',
                this.autoplay.onVisibilityChange
              )
          },
        },
      },
      {
        name: 'effect-fade',
        params: { fadeEffect: { crossFade: !1 } },
        create: function () {
          n.extend(this, {
            fadeEffect: {
              setTranslate: ve.setTranslate.bind(this),
              setTransition: ve.setTransition.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            if ('fade' === this.params.effect) {
              this.classNames.push(this.params.containerModifierClass + 'fade')
              var e = {
                slidesPerView: 1,
                slidesPerColumn: 1,
                slidesPerGroup: 1,
                watchSlidesProgress: !0,
                spaceBetween: 0,
                virtualTranslate: !0,
              }
              n.extend(this.params, e), n.extend(this.originalParams, e)
            }
          },
          setTranslate: function () {
            'fade' === this.params.effect && this.fadeEffect.setTranslate()
          },
          setTransition: function (e) {
            'fade' === this.params.effect && this.fadeEffect.setTransition(e)
          },
        },
      },
      {
        name: 'effect-cube',
        params: {
          cubeEffect: {
            slideShadows: !0,
            shadow: !0,
            shadowOffset: 20,
            shadowScale: 0.94,
          },
        },
        create: function () {
          n.extend(this, {
            cubeEffect: {
              setTranslate: fe.setTranslate.bind(this),
              setTransition: fe.setTransition.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            if ('cube' === this.params.effect) {
              this.classNames.push(this.params.containerModifierClass + 'cube'),
                this.classNames.push(this.params.containerModifierClass + '3d')
              var e = {
                slidesPerView: 1,
                slidesPerColumn: 1,
                slidesPerGroup: 1,
                watchSlidesProgress: !0,
                resistanceRatio: 0,
                spaceBetween: 0,
                centeredSlides: !1,
                virtualTranslate: !0,
              }
              n.extend(this.params, e), n.extend(this.originalParams, e)
            }
          },
          setTranslate: function () {
            'cube' === this.params.effect && this.cubeEffect.setTranslate()
          },
          setTransition: function (e) {
            'cube' === this.params.effect && this.cubeEffect.setTransition(e)
          },
        },
      },
      {
        name: 'effect-flip',
        params: { flipEffect: { slideShadows: !0, limitRotation: !0 } },
        create: function () {
          n.extend(this, {
            flipEffect: {
              setTranslate: me.setTranslate.bind(this),
              setTransition: me.setTransition.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            if ('flip' === this.params.effect) {
              this.classNames.push(this.params.containerModifierClass + 'flip'),
                this.classNames.push(this.params.containerModifierClass + '3d')
              var e = {
                slidesPerView: 1,
                slidesPerColumn: 1,
                slidesPerGroup: 1,
                watchSlidesProgress: !0,
                spaceBetween: 0,
                virtualTranslate: !0,
              }
              n.extend(this.params, e), n.extend(this.originalParams, e)
            }
          },
          setTranslate: function () {
            'flip' === this.params.effect && this.flipEffect.setTranslate()
          },
          setTransition: function (e) {
            'flip' === this.params.effect && this.flipEffect.setTransition(e)
          },
        },
      },
      {
        name: 'effect-coverflow',
        params: {
          coverflowEffect: {
            rotate: 50,
            stretch: 0,
            depth: 100,
            modifier: 1,
            slideShadows: !0,
          },
        },
        create: function () {
          n.extend(this, {
            coverflowEffect: {
              setTranslate: ge.setTranslate.bind(this),
              setTransition: ge.setTransition.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            'coverflow' === this.params.effect &&
              (this.classNames.push(
                this.params.containerModifierClass + 'coverflow'
              ),
              this.classNames.push(this.params.containerModifierClass + '3d'),
              (this.params.watchSlidesProgress = !0),
              (this.originalParams.watchSlidesProgress = !0))
          },
          setTranslate: function () {
            'coverflow' === this.params.effect &&
              this.coverflowEffect.setTranslate()
          },
          setTransition: function (e) {
            'coverflow' === this.params.effect &&
              this.coverflowEffect.setTransition(e)
          },
        },
      },
      {
        name: 'thumbs',
        params: {
          thumbs: {
            multipleActiveThumbs: !0,
            swiper: null,
            slideThumbActiveClass: 'swiper-slide-thumb-active',
            thumbsContainerClass: 'swiper-container-thumbs',
          },
        },
        create: function () {
          n.extend(this, {
            thumbs: {
              swiper: null,
              init: be.init.bind(this),
              update: be.update.bind(this),
              onThumbClick: be.onThumbClick.bind(this),
            },
          })
        },
        on: {
          beforeInit: function () {
            var e = this.params.thumbs
            e && e.swiper && (this.thumbs.init(), this.thumbs.update(!0))
          },
          slideChange: function () {
            this.thumbs.swiper && this.thumbs.update()
          },
          update: function () {
            this.thumbs.swiper && this.thumbs.update()
          },
          resize: function () {
            this.thumbs.swiper && this.thumbs.update()
          },
          observerUpdate: function () {
            this.thumbs.swiper && this.thumbs.update()
          },
          setTransition: function (e) {
            var t = this.thumbs.swiper
            t && t.setTransition(e)
          },
          beforeDestroy: function () {
            var e = this.thumbs.swiper
            e && this.thumbs.swiperCreated && e && e.destroy()
          },
        },
      },
    ]
  return (
    void 0 === W.use &&
      ((W.use = W.Class.use), (W.installModule = W.Class.installModule)),
    W.use(we),
    W
  )
})
//# sourceMappingURL=

$(document).ready(function () {
  $('select').not('.js-native-select').niceSelect()

  $(function () {
    $('[beforeAfterSlide_JS]').twentytwenty({
      no_overlay: true,
    })
  })

  $('[promotionsSlider_JS]').each(function () {
    console.log('🎠 Инициализация карусели promotions')
    console.log('Swiper доступен:', typeof Swiper !== 'undefined')

    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    console.log('Контейнер:', $container.length)
    console.log('Кнопки:', $prevBtn.length, $nextBtn.length)
    console.log('Пагинация:', $pagination.length)

    var space = $(window).innerWidth()
    space = (space / 100) * 1.8392012611665

    var swiper = new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      autoplay: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn[0], // Нативный элемент
        prevEl: $prevBtn[0], // Нативный элемент
      },

      pagination: {
        el: $pagination[0], // Нативный элемент
        type: 'bullets',
        clickable: true, // Включаем кликабельность пагинации
      },
      breakpoints: {
        769: {
          slidesPerView: 3,
          spaceBetween: space,
        },
      },
    })
  })

  $('[singleSlider-1_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    var space = $(window).innerWidth()
    space = (space / 100) * 1.8392012611665

    new Swiper($container[0], {
      // Используем [0] для получения нативного DOM-элемента
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn[0], // Нативный элемент
        prevEl: $prevBtn[0], // Нативный элемент
      },
      pagination: {
        el: $pagination[0], // Нативный элемент
        type: 'bullets',
        clickable: true, // Включаем кликабельность пагинации
      },
      breakpoints: {
        769: {
          slidesPerView: 1,
          spaceBetween: space,
        },
      },
    })
  })

  $('[singleSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    new Swiper($container[0], {
      // Используем нативный элемент
      direction: 'horizontal',
      slidesPerView: 3, // По умолчанию 3 слайда
      autoplay: {
        delay: 3000, // Задержка 3 секунды для автопрокрутки
        disableOnInteraction: false, // Автопрокрутка не отключается при взаимодействии
      },
      speed: 500,
      loop: true,
      observer: true,
      observeParents: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn[0], // Нативный элемент
        prevEl: $prevBtn[0], // Нативный элемент
      },
      pagination: {
        el: $pagination[0], // Нативный элемент
        type: 'bullets',
        clickable: true, // Кликабельная пагинация
      },
      breakpoints: {
        320: {
          slidesPerView: 1, // 1 слайд на мобильных
          spaceBetween: 5,
        },
        768: {
          slidesPerView: 2, // 2 слайда на планшетах
          spaceBetween: 10,
        },
        1024: {
          slidesPerView: 3, // 3 слайда на десктопах
          spaceBetween: 10,
        },
      },
    })
  })

  $('[singleSliderNoLoop_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      observer: true,
      observeParents: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
    })
  })

  $('[filtersSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn')

    if ($(window).innerWidth() < 769) {
      var space = $(window).innerWidth()
      space = (space / 100) * 1.875
    } else {
      var space = $(window).innerWidth()
      space = (space / 100) * 2.2070415133998
    }

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 3,
      speed: 500,
      spaceBetween: space,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      breakpoints: {
        769: {
          slidesPerView: 5,
          spaceBetween: space,
        },
      },
    })
  })

  $('[partnersSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 2,
      speed: 500,
      loop: true,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
      breakpoints: {
        769: {
          slidesPerView: 8,
        },
      },
    })
  })

  $('[beforeAfterSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      spaceBetween: 10,
      noSwipingClass: 'twentytwenty-handle',
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
    })
  })

  $('[ourWorksSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    var space = $(window).innerWidth()
    space = (space / 100) * 1.8392012611665

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
      breakpoints: {
        769: {
          slidesPerView: 3,
          spaceBetween: space,
        },
      },
    })
  })

  $('[teamSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    var space = $(window).innerWidth()
    space = (space / 100) * 1.576458223857

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
      breakpoints: {
        769: {
          slidesPerView: 4,
          spaceBetween: space,
        },
      },
    })
  })

  $('[workStepsSlider_JS]').each(function () {
    var $container = $(this).find('.swiper-container'),
      $prevBtn = $(this).find('.slider-prev-btn'),
      $nextBtn = $(this).find('.slider-next-btn'),
      $pagination = $(this).find('.slider-pagination')

    var space = $(window).innerWidth()
    space = (space / 100) * 4.203888596952180767209668943773

    new Swiper($container, {
      direction: 'horizontal',
      slidesPerView: 1,
      speed: 500,
      loop: true,
      spaceBetween: 10,
      navigation: {
        nextEl: $nextBtn,
        prevEl: $prevBtn,
      },
      pagination: {
        el: $pagination,
        type: 'bullets',
      },
      breakpoints: {
        769: {
          slidesPerView: 3,
          spaceBetween: space,
        },
      },
    })
  })

  $('body').on('click', '[agreementControl_JS]', function () {
    var $this = $(this),
      $form = $this.closest('form'),
      $input = $form.find('[agreementControlCheckbox_JS]'),
      $btn = $form.find('[agreementControlBtn_JS]')

    if ($this.hasClass('checked')) {
      $this.removeClass('checked')
      $input.removeAttr('checked')
      $btn.addClass('disabled')
    } else {
      $this.addClass('checked')
      $input.attr('checked', '')
      $btn.removeClass('disabled')
    }
  })

  $('body').on('click', '[slideToggleBtn_JS]', function () {
    var $parent = $(this).closest('[slideToggleContainer_JS]'),
      $target = $parent.find('[slideToggleTarget_JS]')

    $parent.find('[slideToggleBtn_JS]').each(function () {
      $(this).toggleClass('open')
    })
    $target.slideToggle(300)
  })

  $('body [videoBtn_JS]').hover(
    function () {
      var $parent = $(this).closest('[videoContainer_JS]')

      $parent.addClass('hovered')
    },
    function () {
      var $parent = $(this).closest('[videoContainer_JS]')

      $parent.removeClass('hovered')
    }
  )

  $('body').on('click', '[readMoreBtn_JS]', function () {
    var $btn = $(this),
      $parent = $btn.closest('[readMoreContainer_JS]')

    $parent.addClass('open')
  })

  $('[hiddenImgContainer_JS]').each(function () {
    var $container = $(this),
      $count = parseInt($container.attr('hiddenImgContainer_JS')),
      $hint = $container.find('[hiddenImgHint_JS]')

    if ($container.find('>a').length > $count) {
      $hint.find('span').html($container.find('a').length - $count)
      $hint.removeClass('hidden')
    }
  })

  let calcBannerTime = $('[calcBannerFixed_JS]').data('time') * 1000
  setTimeout(function () {
    $('[calcBannerFixed_JS]').addClass('visible')
  }, calcBannerTime)

  setTimeout(function () {
    $('[sideFixedLinks_JS]').addClass('visible')
  }, 3000)

  $('[calcBannerFixedClose_JS]').on('click', function () {
    $(this).closest('[calcBannerFixed_JS]').removeClass('visible')
  })

  $('[sideFixedLinksClose_JS]').on('click', function () {
    $(this).toggleClass('closed')
    $(this)
      .closest('[sideFixedLinks_JS]')
      .find('[sideFixedLinksContainer_JS]')
      .slideToggle(300)
  })

  $('[toTop_JS]').click(function () {
    $('html, body').animate(
      {
        scrollTop: 0,
      },
      0
    )
    return false
  })

  $('[anchorLink_JS]').on('click', function (event) {
    event.preventDefault()
    var id = $(this).attr('href')

    if ($(window).innerWidth() < 769) {
      var headerHeight = $(window).innerWidth()
      headerHeight = (headerHeight / 100) * 29.6875
    } else {
      var headerHeight = $(window).innerWidth()
      ;(headerHeight = (headerHeight / 100) * 5),
        2548607461902259590120861797162
    }

    if ($(id).length > 0) {
      var top = $(id).offset().top - headerHeight
      $('body,html').animate(
        {
          scrollTop: top,
        },
        1500
      )
    } else {
      //             console.log(1);
      window.location.href = 'index.html' + id
    }
  })

  var hash = window.location.hash
  if (hash != '') {
    if ($(window).innerWidth() < 769) {
      var headerHeight = $(window).innerWidth()
      headerHeight = (headerHeight / 100) * 29.6875
    } else {
      var headerHeight = $(window).innerWidth()
      ;(headerHeight = (headerHeight / 100) * 5),
        2548607461902259590120861797162
    }

    if ($(hash).length > 0) {
      var offset = $(hash).offset().top - headerHeight
      $('body,html').animate(
        {
          scrollTop: offset,
        },
        1500
      )
    }
  }
  var stepTotal = $('.calc-price__container').data('questions-count')
  console.log(stepTotal)
  var progressBegin = (1 / stepTotal) * 100
  progressBegin = Math.round(progressBegin)
  $('.progress-bar').css('width', progressBegin + '%')
  $('.progress-text span').html(progressBegin)

  $('[data-target-question]').on('click', function () {
    var $element = $(this),
      $current_container = $element.closest('[data-question-id]'),
      $current_id = $current_container.attr('data-question-id'),
      $target = $element.attr('data-target-question'),
      $container = $element.closest('[data-questions-count]'),
      $count = parseInt($container.attr('data-questions-count'))
    ;($step = parseInt(
      $container
        .find('[data-question-id="' + $target + '"')
        .attr('data-question-step')
    )),
      ($next_tab = $container.find('[data-question-id="' + $target + '"'))

    if ($step < $count) {
      setTimeout(function () {
        if ($(window).innerWidth() < 769) {
          var headerHeight = $(window).innerWidth()
          headerHeight = (headerHeight / 100) * 29.6875
        } else {
          var headerHeight = $(window).innerWidth()
          headerHeight =
            (headerHeight / 100) * 5.2548607461902259590120861797162
        }

        var top = $('#quiz').offset().top - headerHeight
        $('body,html').animate(
          {
            scrollTop: top,
          },
          500
        )

        $container.find('[data-question-hint].active').removeClass('active')
        $container
          .find('[data-question-hint="' + $target + '"')
          .addClass('active')

        $container.find('[data-question-id].active').removeClass('active')

        if ($element.hasClass('next-btn')) {
          $next_tab
            .find('[data-target-question].prev-btn')
            .attr('data-target-question', $current_id)
        }

        if ($element.hasClass('prev-btn')) {
          $current_container.find('.checked').each(function () {
            $(this).removeClass('checked')
          })
          $current_container.find('[checked]').each(function () {
            $(this).removeAttr('checked')
          })

          $current_container.find('input[type="text"]').each(function () {
            $(this).val('')
          })

          $current_container.find('input[type="file"]').each(function () {
            var $element = $(this),
              $text = $element.parent().attr('data-text')

            $element.val('')
            $element.parent().find('div p').text($text)
            $element.parent().find('div').removeClass('active')
          })

          $current_container.find('[data-question-skip]').removeClass('checked')
          $current_container
            .find('[data-question-skip] input')
            .removeAttr('checked')

          $current_container
            .find('[data-target-question].next-btn')
            .parent()
            .addClass('active')
          $current_container
            .find('[data-target-question].next-btn')
            .addClass('disabled')
        }

        $next_tab.addClass('active')

        var progress = ($step / $count) * 100
        progress = Math.round(progress)

        if (progress >= 60) {
          $container.find('.progress-text').addClass('md-white-text')
        } else {
          $container.find('.progress-text').removeClass('md-white-text')
        }

        $container.find('.progress-bar').css('width', progress + '%')
        $container.find('.progress-text span').html(progress)
      }, 500)
    } else if ($step == $count) {
      $container.find('.calc-price__preloader').addClass('active')

      if ($(window).innerWidth() < 769) {
        var headerHeight = $(window).innerWidth()
        headerHeight = (headerHeight / 100) * 29.6875
      } else {
        var headerHeight = $(window).innerWidth()
        headerHeight = (headerHeight / 100) * 5.2548607461902259590120861797162
      }

      var top = $('#quiz').offset().top - headerHeight
      $('body,html').animate(
        {
          scrollTop: top,
        },
        500
      )

      setTimeout(function () {
        $container.find('.calc-price__preloader').removeClass('active')

        $container.find('[data-question-hint].active').removeClass('active')
        $container
          .find('[data-question-hint="' + $target + '"')
          .addClass('active')
        $container.find('[data-question-id].active').removeClass('active')
        $container
          .find('[data-question-id="' + $target + '"')
          .addClass('active')

        var progress = 100

        $container.addClass('md-final-step')
        $container.find('.progress-text').addClass('md-white-text')
        $container.find('.progress-bar').addClass('md-100-width')
        $container.find('.progress-bar').css('width', progress + '%')
        $container.find('.progress-text span').html(progress)
      }, 2500)
    }
  })

  $('[data-question-skip]').on('click', function () {
    var $element = $(this),
      $container = $element.closest('[data-question-id]')

    if ($element.hasClass('disabled')) {
      if ($element.hasClass('checked')) {
        $element.find('input').removeAttr('checked')
        $element.removeClass('checked')
      } else {
        $element.find('input').attr('checked', 'checked')
        $element.addClass('checked')
      }
    } else if ($element.hasClass('checked')) {
      $element.find('input').removeAttr('checked')
      $element.removeClass('checked')

      $container
        .find('[data-target-question].next-btn')
        .parent()
        .addClass('active')
      $container.find('[data-target-question].next-btn').addClass('disabled')
    } else {
      $element.find('input').attr('checked', 'checked')
      $element.addClass('checked')

      $container
        .find('[data-target-question].disabled')
        .parent()
        .removeClass('active')
      $container.find('[data-target-question].disabled').removeClass('disabled')
    }
  })

  $('[data-quiz-input]').on('input', function () {
    var $element = $(this),
      $container = $element.closest('[data-question-id]')

    $container.find('[data-question-skip] input').removeAttr('checked')
    $container.find('[data-question-skip]').removeClass('checked')

    var count = 0

    $container.find('[data-quiz-input]').each(function () {
      if ($(this).val().length < 1) {
        count = count + 1
      }
    })

    if (count == 0) {
      $container
        .find('[data-target-question].disabled')
        .parent()
        .removeClass('active')
      $container.find('[data-target-question].disabled').removeClass('disabled')
      $container.find('[data-question-skip]').addClass('disabled')
    } else {
      $container
        .find('[data-target-question].next-btn')
        .parent()
        .addClass('active')
      $container.find('[data-target-question].next-btn').addClass('disabled')
      $container.find('[data-question-skip]').removeClass('disabled')
    }
  })

  $('[quizSingleAnswer_JS]').on('click', function () {
    var $element = $(this),
      $container = $element.closest('[data-question-id]'),
      $target = $element.attr('quizSingleAnswer_JS')

    $container.find('input').removeAttr('checked')
    $container.find('[quizSingleAnswer_JS].checked').removeClass('checked')

    $element.addClass('checked')
    $element.find('input').attr('checked', 'checked')

    $container
      .find('[data-target-question].disabled')
      .parent()
      .removeClass('active')
    $container.find('[data-target-question].disabled').removeClass('disabled')

    $container
      .find('[data-target-question].next-btn')
      .attr('data-target-question', $target)
    setTimeout(function () {
      $container.find('[data-target-question].next-btn').trigger('click')
    }, 250)
  })

  $('[quizMultipleAnswer_JS]').on('click', function () {
    var $element = $(this),
      $container = $element.closest('[data-question-id]')

    if ($element.hasClass('checked')) {
      $element.removeClass('checked')
      $element.find('input').removeAttr('checked')
    } else {
      $element.addClass('checked')
      $element.find('input').attr('checked', 'checked')
    }

    $container
      .find('[data-target-question].disabled')
      .parent()
      .removeClass('active')
    $container.find('[data-target-question].disabled').removeClass('disabled')

    if ($container.find('[quizMultipleAnswer_JS].checked').length < 1) {
      $container
        .find('[data-target-question].next-btn')
        .parent()
        .addClass('active')
      $container.find('[data-target-question].next-btn').addClass('disabled')
    }
  })

  $('[quizSocialSelect_JS]').on('click', function () {
    var $element = $(this),
      $value = $element.find('input').val(),
      $attrCheck = $element.attr('quizSocialEmailSelect_JS'),
      $container = $('[quizSocialContainer_JS]')

    if (typeof $attrCheck !== 'undefined' && $attrCheck !== false) {
      $container.find('[quizSocialEmailInput_JS]').removeClass('hidden')
      $container.find('[quizSocialSelect_JS].active').removeClass('active')
      $element.addClass('active')
      $container.find('[quizSocialInput_JS]').attr('placeholder', 'Ваш телефон')
    } else {
      $container.find('[quizSocialEmailInput_JS]').val('')
      $container.find('[quizSocialEmailInput_JS]').addClass('hidden')
      $container.find('[quizSocialSelect_JS].active').removeClass('active')
      $element.addClass('active')
      $container
        .find('[quizSocialInput_JS]')
        .attr('placeholder', 'Ваш телефон в ' + $value)
    }
  })

  $('[data-quiz-file-input]').change(function (e) {
    var $element = $(this),
      $label = $element.closest('label'),
      $container = $element.closest('[data-question-id]')

    $container.find('[data-question-skip] input').removeAttr('checked')
    $container.find('[data-question-skip]').removeClass('checked')

    if (typeof e.target.files[0] == 'undefined') {
      $label.find('div').removeClass('active')
      $container
        .find('[data-target-question].next-btn')
        .parent()
        .addClass('active')
      $container.find('[data-target-question].next-btn').addClass('disabled')
      $container.find('[data-question-skip]').removeClass('disabled')
    } else {
      $label.find('div').addClass('active')
      $container
        .find('[data-target-question].next-btn')
        .parent()
        .removeClass('active')
      $container.find('[data-target-question].next-btn').removeClass('disabled')
      $container.find('[data-question-skip]').addClass('disabled')
    }
  })

  $('input[type="file"]').change(function (e) {
    var text = $(this).closest('label').attr('data-text')
    if (typeof e.target.files[0] == 'undefined') {
      var fileName = text
    } else {
      var fileName = e.target.files[0].name
    }
    $(this).parent().find('div p').text(fileName)
  })

  $('[formStars_JS] label').on('click', function () {
    var $label = $(this),
      $value = parseInt($label.find('input').attr('value'))

    $label
      .closest('[formStars_JS]')
      .find('label')
      .each(function () {
        ;($label_2 = $(this)),
          ($value_2 = parseInt($label_2.find('input').attr('value')))

        if ($value_2 > $value) {
          $label_2.addClass('disabled')
        } else {
          $label_2.removeClass('disabled')
        }
      })
  })

  $('[formValidate_JS]').each(function () {
    var form = $(this)
    $(this).validate({
      errorClass: 'validate_error',
      rules: {
        phone: {
          required: true,
          minlength: 2,
        },
        name: {
          required: true,
          minlength: 1,
        },
        email: {
          required: true,
          minlength: 1,
        },
        city: {
          required: true,
          minlength: 1,
        },
        message: {
          required: true,
          minlength: 1,
        },
      },
      errorPlacement: function (error, element) {},
    })
  })

  $(window).scroll(function () {
    var scroll = $(window).scrollTop()

    var header = $(window).innerWidth()
    ;(header = (header / 100) * 7), 8822911192853389385181292695744

    //         if (scroll > header) {
    //             $('[headerFixed_JS]').addClass('fixed');
    //         } else {
    //             $('[headerFixed_JS]').removeClass('fixed');
    //         };

    if (scroll > 500) {
      $('[toTop_JS]').addClass('visible')
    } else {
      $('[toTop_JS]').removeClass('visible')
    }

    $('[onScrollDisplay_JS]').each(function () {
      var el = $(this)

      if (come(el)) {
        el.addClass('visible')
      }
    })

    if ($(window).innerWidth() < 769) {
      if (scroll > 0) {
        $('header').addClass('fixed')
      } else {
        $('header').removeClass('fixed')
      }
    }
  })

  $(window).on('load', function () {
    var scroll = $(window).scrollTop()

    var header = $(window).innerWidth()
    ;(header = (header / 100) * 7), 8822911192853389385181292695744

    //         if (scroll > header) {
    //             $('[headerFixed_JS]').addClass('fixed');
    //         } else {
    //             $('[headerFixed_JS]').removeClass('fixed');
    //         };

    if (scroll > 500) {
      $('[toTop_JS]').addClass('visible')
    } else {
      $('[toTop_JS]').removeClass('visible')
    }

    $('[onScrollDisplay_JS]').each(function () {
      var el = $(this)

      if (come(el)) {
        el.addClass('visible')
      }
    })

    if ($(window).innerWidth() < 769) {
      if (scroll > 0) {
        $('header').addClass('fixed')
      } else {
        $('header').removeClass('fixed')
      }
    }
  })

  $('[loadMoreBtn_JS]').on('click', function () {
    $(this).closest('[loadMoreContainer_JS]').addClass('md-all-items-visible')
  })

  if ($(window).innerWidth() < 769) {
    $('.portfolio.md-mobile-slider').each(function () {
      var $elem = $(this)

      $elem.find('.portfolio__item').each(function () {
        var $slide = $(this).clone()
        $elem.find('.swiper-wrapper').append('<div class="swiper-slide"></div>')
        $elem.find('.swiper-wrapper .swiper-slide:last-child').append($slide)
      })

      $elem.find('.portfolio__slider').each(function () {
        var $container = $(this).find('.swiper-container'),
          $pagination = $(this).find('.slider-pagination')

        new Swiper($container, {
          direction: 'horizontal',
          slidesPerView: 1,
          speed: 500,
          loop: true,
          spaceBetween: 20,
          pagination: {
            el: $pagination,
            type: 'bullets',
          },
        })
      })
    })

    $('.articles.md-mobile-slider').each(function () {
      var $elem = $(this)

      $elem.find('.articles__item').each(function () {
        var $slide = $(this).clone()
        $elem.find('.swiper-wrapper').append('<div class="swiper-slide"></div>')
        $elem.find('.swiper-wrapper .swiper-slide:last-child').append($slide)
      })

      $elem.find('.articles__slider').each(function () {
        var $container = $(this).find('.swiper-container'),
          $pagination = $(this).find('.slider-pagination')

        new Swiper($container, {
          direction: 'horizontal',
          slidesPerView: 1,
          speed: 500,
          loop: true,
          spaceBetween: 20,
          pagination: {
            el: $pagination,
            type: 'bullets',
          },
        })
      })
    })

    $('.our-values').each(function () {
      var $elem = $(this)

      $elem.find('.our-values__item').each(function () {
        var $slide = $(this).clone()
        $elem.find('.swiper-wrapper').append('<div class="swiper-slide"></div>')
        $elem.find('.swiper-wrapper .swiper-slide:last-child').append($slide)
      })

      $elem.find('.our-values__slider').each(function () {
        var $container = $(this).find('.swiper-container'),
          $pagination = $(this).find('.slider-pagination')

        new Swiper($container, {
          direction: 'horizontal',
          slidesPerView: 1,
          speed: 500,
          loop: true,
          spaceBetween: 10,
          pagination: {
            el: $pagination,
            type: 'bullets',
          },
        })
      })
    })

    $('.other-reviews').each(function () {
      var $elem = $(this),
        $mainSlider = $elem.find('.other-reviews__slider')

      $elem.find('.other-reviews__item').each(function () {
        var $slide = $(this).clone()
        $mainSlider
          .find('>.swiper-container>.swiper-wrapper')
          .append('<div class="swiper-slide"></div>')
        $mainSlider
          .find('>.swiper-container>.swiper-wrapper .swiper-slide:last-child')
          .append($slide)
      })

      $mainSlider.each(function () {
        var $container = $(this).find('>.swiper-container'),
          $pagination = $(this).find('>.slider-pagination')

        new Swiper($container, {
          direction: 'horizontal',
          slidesPerView: 1,
          speed: 500,
          spaceBetween: 10,
          pagination: {
            el: $pagination,
            type: 'bullets',
          },
        })
      })
    })

    $('.review-content__img-container').each(function () {
      var $elem = $(this),
        $parent = $elem.closest('.other-reviews__item'),
        $slider = $parent.find('.review-content__slider')

      $elem.find('>a').each(function () {
        var $slide = $(this).clone()
        $slider
          .find('.swiper-wrapper')
          .append('<div class="swiper-slide"></div>')
        $slider.find('.swiper-wrapper .swiper-slide:last-child').append($slide)
      })

      $slider.each(function () {
        var $container = $(this).find('.swiper-container'),
          $pagination = $(this).find('.slider-pagination')

        var space = $(window).innerWidth()
        space = (space / 100) * 3.75

        new Swiper($container, {
          direction: 'horizontal',
          slidesPerView: 2,
          speed: 500,
          spaceBetween: space,
          pagination: {
            el: $pagination,
            type: 'bullets',
          },
        })
      })
    })

    $('.clients-choose').each(function () {
      $(this)
        .find('.bullets-item')
        .each(function () {
          var $this = $(this),
            $parent = $this.closest('.clients-choose'),
            $slider = $parent.find('.swiper-wrapper')

          $slider.append('<div class="swiper-slide"></div>')
          $slider.find('.swiper-slide:last-child').append($this.clone())
        })

      $(this)
        .find('.clients-choose__slider')
        .each(function () {
          var $container = $(this).find('.swiper-container'),
            $pagination = $(this).find('.slider-pagination')

          var space = $(window).innerWidth()
          space = (space / 100) * 3.125

          new Swiper($container, {
            direction: 'horizontal',
            slidesPerView: 2,
            speed: 500,
            spaceBetween: space,
            pagination: {
              el: $pagination,
              type: 'bullets',
            },
          })
        })
    })

    $('.portfolio-typical__img.md-3-col-img').each(function () {
      $(this)
        .find('>a')
        .each(function () {
          var $this = $(this),
            $parent = $this.closest('.portfolio-typical__img'),
            $slider = $parent.find('.swiper-wrapper')

          $slider.append('<div class="swiper-slide"></div>')
          $slider.find('.swiper-slide:last-child').append($this.clone())
        })

      $(this)
        .find('.portfolio-typical__img-slider')
        .each(function () {
          var $container = $(this).find('.swiper-container'),
            $pagination = $(this).find('.slider-pagination')

          var space = $(window).innerWidth()
          space = (space / 100) * 3.4375

          new Swiper($container, {
            direction: 'horizontal',
            slidesPerView: 2,
            speed: 500,
            spaceBetween: space,
            pagination: {
              el: $pagination,
              type: 'bullets',
            },
          })
        })
    })

    if (
      $('.calc-banner.md-calc-banner-small').length > 0 &&
      $('.side-banner').length > 0
    ) {
      if ($('.calc-banner').length > 1) {
        $('.calc-banner.md-calc-banner-small').remove()
      } else {
        $('.calc-banner.md-calc-banner-small').removeClass(
          'md-calc-banner-small'
        )
        $($('.calc-banner')).insertBefore('footer')
      }
      $($('.side-banner')).insertBefore('footer')
    }
  }
})

// Функции бургер меню - перемещены внутрь document.ready
function menuOpen() {
  $('html').css('overflow', 'hidden')
  $('[headerBtn_JS]').addClass('open')
  $('[headerBtn_JS]').closest('header').addClass('open')
}

function menuClose() {
  $('html').removeAttr('style')
  $('[headerBtn_JS]').removeClass('open')
  $('[headerBtn_JS]').closest('header').removeClass('open')
}

function come(elem) {
  var docViewTop = $(window).scrollTop(),
    docViewBottom = docViewTop + $(window).height(),
    elemTop = $(elem).offset().top,
    elemBottom = elemTop + $(elem).height()

  return elemBottom <= docViewBottom && elemTop >= docViewTop
}

$(document).ready(function () {
  // Инициализация бургер меню
  $('[headerBtn_JS]').on('click', function () {
    if ($(this).hasClass('open')) {
      menuClose()
    } else {
      menuOpen()
    }
  })

  $('.header__nav a').on('click', function () {
    if ($('[headerBtn_JS]').hasClass('open')) {
      menuClose()
    }
  })

  $('.nav-item.dropdown-item').on('click', function () {
    if ($(window).innerWidth() < 769) {
      $(this).find('.nav-menu').toggleClass('open')
    }
  })

  $('.nav-item.dropdown-item a').on('click', function (event) {
    event.stopPropagation()
  })

  $('body').on('click', '[popupOpen]', function (e) {
    // Если это <a>, предотвращаем переход по ссылке
    if (this.tagName && this.tagName.toLowerCase() === 'a') {
      e.preventDefault()
    }
    popupClose()
    var $elem = $(this),
      target = $elem.attr('popupOpen')

    // Прокидываем источник формы для новых попапов
    try {
      var source = $elem.attr('data-form-source') || ''
      if (source) {
        var popupEl = document.getElementById(target)
        if (popupEl) {
          var form = popupEl.querySelector('.popup__form')
          if (form) {
            var input = form.querySelector('input[name="form_source"]')
            if (!input) {
              input = document.createElement('input')
              input.type = 'hidden'
              input.name = 'form_source'
              form.prepend(input)
            }
            input.value = source
          }
        }
      }
    } catch (e) {}

    if (
      target == 'promotion-popup' ||
      target == 'service-popup' ||
      target == 'product-popup'
    ) {
      var id = $elem.attr('data-hidden-id')

      $('[popupID="' + target + '"]')
        .find('[hiddenValue_JS]')
        .attr('value', id)
    }

    popupOpen(target)
  })

  $('body').on('click', '[popupClose_JS]', function () {
    popupClose()
  })

  $('body').append('<div class="download-popup-trigger"></div>')

  $('body .download-popup-trigger').hover(function () {
    if (localStorage.getItem('downloadPopupState') === 'true') {
      popupOpen('download-popup')
      localStorage.setItem('downloadPopupState', 'false')
    }
  })

  // popupClose();
})

if (localStorage.getItem('downloadPopupState') === null) {
  localStorage.setItem('downloadPopupState', 'true')
}

var popupClose = function () {
  // Закрываем и старые, и новые попапы
  var $openedLegacy = $('.popup.open')
  var $openedNew = $('.popup.active')

  $openedLegacy.find('input').val('')
  $openedLegacy.find('textarea').val('')
  $openedLegacy.find('.form-file').each(function () {
    var text = $(this).attr('data-text')

    $(this).find('div p').text(text)
  })
  $openedLegacy.find('.form-stars label:last-child').trigger('click')
  $('.popup .validate_error').each(function () {
    $(this).removeClass('validate_error')
  })
  $('html').removeAttr('style')
  document.body.style.overflow = ''
  $openedLegacy.removeClass('open')
  $openedNew.removeClass('active')
}

var popupOpen = function (e) {
  popupClose()
  var target = e
  var width = $('.page').width()

  $('html').css('overflow', 'hidden')
  document.body.style.overflow = 'hidden'

  // Поддержка НОВЫХ попапов (по id)
  var newPopup = document.getElementById(target)
  if (newPopup) {
    newPopup.classList.add('active')
    return
  }

  // Поддержка СТАРЫХ попапов (по атрибуту popupID)
  $('[popupID="' + target + '"]').addClass('open')
}

// Cookie consent code moved to new-homepage.js to avoid conflicts
