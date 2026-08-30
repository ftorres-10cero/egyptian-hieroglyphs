require('../tests/node-shims')();
const mdc = require('../assets/mdcconversion.js');
const convert = (line) => {
  try {
    const p = mdc.parse(line + '\n');
    const out = (p.parts||[]).map(x => x.cutByColor().map(f=>f.toString()).join('')).join('');
    return out.includes('\uFFFD') ? 'FFFD' : out;
  } catch(e) { return 'SYNTAX'; }
};
const tests = ["<- nTr:r ->", "<- nTr:r:x ->", "<- nTr:r:x:t ->", "<- Htp-di ->", "<- mn-xpr-ra ->", "<S- anx ->", "<F- anx ->", "<H- anx ->", "<-s-anx->", "<-b-anx->", "<-H-anx->", "<-1-anx->", "nTr\\r1", "nTr\\R90", "nTr\\R180", "nTr\\R270", "nTr\\h", "nTr\\v", "nTr\\t1", "nTr\\shading1234"];
for (const t of tests) {
  const r = convert(t);
  console.log(JSON.stringify(t).padEnd(24), r === 'FFFD' || r === 'SYNTAX' ? r : JSON.stringify(r));
}
