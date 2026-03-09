const bcrypt = require("bcryptjs");

const users = [
  { login: "mdubois", password: "Mairie#A21pX9" },
  { login: "lmartin", password: "Urban!B34kLm" },
  { login: "sbernard", password: "Agent$C56QrT" },
  { login: "tpetit", password: "Ville%D78sFg" },
  { login: "crobert", password: "Secure&E91hJk" },
  { login: "jrichard", password: "Admin*F12LpQ" },
  { login: "ldurand", password: "Police@G23RtY" },
  { login: "nmoreau", password: "Data!H45UiO" },
  { login: "csimon", password: "Server#J67AzX" },
  { login: "alaurent", password: "Tech$K89QwE" },
  { login: "jlefebvre", password: "Login%L10MnB" },
  { login: "agarcia", password: "Ticket&N21RtV" },
  { login: "sdavid", password: "Bureau*P32DfG" },
  { login: "mroux", password: "System@Q43JkL" },
  { login: "evincent", password: "Password#R54CvB" },
  { login: "pfournier", password: "Network$S65PoI" },
  { login: "emorel", password: "Secure%T76YuH" },
  { login: "hgirard", password: "Service&U87ErT" },
  { login: "mandre", password: "Portal*V98DfQ" },
  { login: "lmercier", password: "Control@W09GhJ" },
  { login: "cdupont", password: "User#X11LmN" },
  { login: "nlambert", password: "Admin$Y22PoQ" },
  { login: "abonnet", password: "Agent%Z33AsD" },
  { login: "vfrancois", password: "Secure&A44GhJ" },
  { login: "lguerin", password: "System&B55JkL" },
  { login: "pbernard", password: "Login&C66QwE" },
  { login: "jmoreau", password: "Access&D77RtY" },
  { login: "mpetit", password: "Bureau&E88PoI" },
  { login: "lsimon", password: "Ticket&F99YuH" },
  { login: "probert", password: "Police*G10ErT" },
  { login: "cdurand", password: "Ville@H21DfQ" },
  { login: "nlefevre", password: "Data#J32GhJ" },
  { login: "slambert", password: "Server$K43LmN" },
  { login: "tgarcia", password: "Tech%L54PoQ" },
  { login: "candre", password: "Secure&M65AsD" },
  { login: "jfrancois", password: "Admin&N76GhJ" },
  { login: "emercier", password: "Portal*P87JkL" },
  { login: "hguerin", password: "System@Q98QwE" },
  { login: "mvincent", password: "Control#R09RtY" },
  { login: "lroux", password: "User$S12PoI" },
  { login: "cdavid", password: "Secure%T23YuH" },
  { login: "ngirard", password: "Network&U34ErT" },
  { login: "amorel", password: "Login*V45DfQ" },
  { login: "vandre", password: "Bureau@W56GhJ" },
  { login: "llaurent", password: "Access#X67LmN" },
  { login: "lbonnet", password: "Ticket$Y78PoQ" },
  { login: "mmercier", password: "Police%Z89AsD" },
  { login: "spetit", password: "Service&A90GhJ" },
  { login: "amoreau", password: "Admin&B12JkL" },
  { login: "jgarcia", password: "Secure@C23QwE" }
];

async function generateHashes() {
  const saltRounds = 10;

  for (const user of users) {
    const hash = await bcrypt.hash(user.password, saltRounds);
    console.log(`${user.login} | ${user.password} | ${hash}`);
  }
}

generateHashes().catch((error) => {
  console.error("Erreur lors de la génération des hashes :", error);
});