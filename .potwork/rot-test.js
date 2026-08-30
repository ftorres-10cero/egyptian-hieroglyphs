require('../tests/node-shims')();
const mdc = require('../assets/mdcconversion.js');
const convert = (line) => {
  try {
    const p = mdc.parse(line + '\n');
    const out = (p.parts||[]).map(x => x.cutByColor().map(f=>f.toString()).join('')).join('');
    return out.includes('\uFFFD') ? 'FFFD' : out;
  } catch(e) { return 'SYNTAX'; }
};
const tests = ["\\r1-nTr", "nTr\\r1", "\\r2-nTr", "\\r3-nTr", "\\r4-nTr", "\\t1-nTr", "\\t2-nTr", "\\t3-nTr", "\\t4-nTr", "\\R90-nTr", "\\R180-nTr", "\\R270-nTr", "\\(90)-nTr", "\\i-nTr", "\\l-nTr", "\\h-nTr", "\\v-nTr", "\\shading1-nTr", "nTr\\shading1"];
for (const t of tests) {
  const r = convert(t);
  console.log(JSON.stringify(t).padEnd(20), r === 'FFFD' || r === 'SYNTAX' ? r : JSON.stringify(r));
}
