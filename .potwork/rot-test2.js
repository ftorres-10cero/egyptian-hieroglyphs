require('../tests/node-shims')();
const mdc = require('../assets/mdcconversion.js');
const convert = (line) => {
  try {
    const p = mdc.parse(line + '\n');
    const out = (p.parts||[]).map(x => x.cutByColor().map(f=>f.toString()).join('')).join('');
    return out.includes('\uFFFD') ? 'FFFD' : out;
  } catch(e) { return 'SYNTAX'; }
};
const tests = ["nTr\\r1", "nTr\\r2", "nTr\\r3", "nTr\\r4", "nTr\\t1", "nTr\\t2", "nTr\\t3", "nTr\\t4", "nTr\\R90", "nTr\\R180", "nTr\\R270", "nTr\\(90)", "nTr\\(180)", "nTr\\(270)", "nTr\\i", "nTr\\l", "nTr\\h", "nTr\\v", "nTr\\shading1", "nTr\\shading2", "nTr\\shading3", "nTr\\shading4", "nTr\\shading1234", "nTr\\shading12", "nTr\\r1\\r1", "nTr\\r2\\r2", "\\red-nTr", "nTr\\red", "\\black-nTr"];
for (const t of tests) {
  const r = convert(t);
  const cps = (r === 'FFFD' || r === 'SYNTAX') ? '' : ' [' + Array.from(r).map(c=>'U+'+c.codePointAt(0).toString(16)).join(' ') + ']';
  console.log(JSON.stringify(t).padEnd(20), (r === 'FFFD' || r === 'SYNTAX' ? r : JSON.stringify(r)) + cps);
}
