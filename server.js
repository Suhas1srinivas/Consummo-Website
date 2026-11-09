const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = 3001;
const PUBLIC_DIR = __dirname;

const MIME_TYPES = {
  '.html': 'text/html; charset=UTF-8',
  '.css': 'text/css; charset=UTF-8',
  '.js': 'application/javascript; charset=UTF-8',
  '.json': 'application/json; charset=UTF-8',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.avif': 'image/avif',
  '.webp': 'image/webp',
  '.woff': 'font/woff',
  '.woff2': 'font/woff2',
  '.ttf': 'font/ttf',
  '.otf': 'font/otf',
  '.mp4': 'video/mp4'
};

// Local stand-in for api/contact.php (PHP runs only on Hostinger).
// Logs the submission and saves it to api/data/leads-local.csv so the forms can be tested locally.
function handleContactLocally(req, res) {
  let raw = '';
  req.on('data', c => { raw += c; if (raw.length > 20000) req.destroy(); });
  req.on('end', () => {
    const p = new URLSearchParams(raw);
    const get = k => (p.get(k) || '').trim();
    const json = (code, body) => { res.writeHead(code, { 'Content-Type': 'application/json; charset=utf-8' }); res.end(JSON.stringify(body)); };
    if (get('website')) return json(200, { ok: true });
    const errors = [];
    if (get('name').length < 2) errors.push('Please enter your name.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(get('email'))) errors.push('Please enter a valid email address.');
    if (get('consent') !== 'yes') errors.push('Please agree to be contacted about your request.');
    if (errors.length) return json(422, { ok: false, error: errors.join(' ') });
    const fields = Object.fromEntries([...new Set(p.keys())].filter(k => !['website', 'elapsed'].includes(k)).map(k => [k, p.getAll(k).join('; ')]));
    console.log('\n[local form] ' + new Date().toISOString(), fields);
    try {
      const dir = path.join(PUBLIC_DIR, 'api', 'data'); fs.mkdirSync(dir, { recursive: true });
      const line = [new Date().toISOString(), ...Object.values(fields)].map(v => '"' + String(v).replace(/"/g, '""') + '"').join(',') + '\n';
      fs.appendFileSync(path.join(dir, 'leads-local.csv'), line);
    } catch (e) { /* ignore */ }
    json(200, { ok: true, local: true });
  });
}

const server = http.createServer((req, res) => {
  let reqPath = decodeURI(req.url.split('?')[0]);

  if (reqPath === '/api/contact.php' && req.method === 'POST') return handleContactLocally(req, res);
  if (reqPath.startsWith('/api/')) { res.writeHead(404, { 'Content-Type': 'text/plain' }); return res.end('404 Not Found'); }
  if (reqPath === '/' || reqPath === '') {
    reqPath = '/index.html';
  }

  let filePath = path.join(PUBLIC_DIR, reqPath);

  // Check if filePath is a directory
  if (fs.existsSync(filePath) && fs.statSync(filePath).isDirectory()) {
    filePath = path.join(filePath, 'index.html');
  }

  // Fallback to index.html if file doesn't exist
  if (!fs.existsSync(filePath)) {
    // If it was requesting an asset, return 404
    const ext = path.extname(reqPath).toLowerCase();
    if (ext && ext !== '.html') {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      return res.end('404 Not Found');
    }
    filePath = path.join(PUBLIC_DIR, 'index.html');
  }

  const ext = path.extname(filePath).toLowerCase();
  const contentType = MIME_TYPES[ext] || 'application/octet-stream';

  // Support Range requests for video streaming (crucial for smooth looping and seeking)
  if (ext === '.mp4') {
    const stat = fs.statSync(filePath);
    const fileSize = stat.size;
    const range = req.headers.range;

    if (range) {
      const parts = range.replace(/bytes=/, "").split("-");
      const start = parseInt(parts[0], 10);
      const end = parts[1] ? parseInt(parts[1], 10) : fileSize - 1;
      const chunksize = (end - start) + 1;
      const file = fs.createReadStream(filePath, { start, end });
      const head = {
        'Content-Range': `bytes ${start}-${end}/${fileSize}`,
        'Accept-Ranges': 'bytes',
        'Content-Length': chunksize,
        'Content-Type': 'video/mp4',
        'Access-Control-Allow-Origin': '*'
      };
      res.writeHead(206, head);
      file.pipe(res);
      return;
    } else {
      const head = {
        'Content-Length': fileSize,
        'Content-Type': 'video/mp4',
        'Accept-Ranges': 'bytes',
        'Access-Control-Allow-Origin': '*'
      };
      res.writeHead(200, head);
      fs.createReadStream(filePath).pipe(res);
      return;
    }
  }

  fs.readFile(filePath, (err, content) => {
    if (err) {
      res.writeHead(500, { 'Content-Type': 'text/plain' });
      return res.end('500 Internal Server Error');
    }

    res.writeHead(200, {
      'Content-Type': contentType,
      'Access-Control-Allow-Origin': '*'
    });
    res.end(content);
  });
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`Consummo website running at http://localhost:${PORT}`);
});

