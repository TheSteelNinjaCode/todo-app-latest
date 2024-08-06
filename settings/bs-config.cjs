const { createProxyMiddleware } = require("http-proxy-middleware");
const fs = require("fs");

const jsonData = fs.readFileSync("prisma-php.json", "utf8");
const config = JSON.parse(jsonData);

module.exports = {
  proxy: "http://localhost:3000",
  middleware: [
    (req, res, next) => {
      res.setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
      res.setHeader("Pragma", "no-cache");
      res.setHeader("Expires", "0");
      next();
    },
    createProxyMiddleware({
      target: config.bsTarget,
      changeOrigin: true,
      pathRewrite: config.bsPathRewrite,
    }),
  ],
  files: "src/**/*.*",
  notify: false,
  open: false,
  ghostMode: false,
};