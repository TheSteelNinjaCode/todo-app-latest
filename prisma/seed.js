import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

// UserRole
const userRoleData = [
  {
    name: "Admin",
  },
  {
    name: "User",
  },
];

// User
const userData = [
  {
    name: "Juan",
    email: "j@gmail.com",
    password: "$2b$10$mgjotYzIXwrK1MCWmu4tgeUVnLcb.qzvqwxOq4FXEL8k2obwXivDi", // TODO: template password 1234 (bcrypt) testing only
    roleId: 1,
  },
];

async function main() {
  // ========================================
  // Code for PostgreSQL
  // ----------------------------------------
  // UserRole
  // ----------------------------------------
  // await prisma.userRole.deleteMany();
  // await prisma.userRole.createMany({ data: userRoleData });
  // await prisma.$executeRaw`ALTER SEQUENCE "UserRole_id_seq" RESTART WITH 1`;
  // ----------------------------------------
  // User
  // ----------------------------------------
  // await prisma.user.deleteMany();
  // await prisma.user.createMany({ data: userData });
  // ========================================
  // Code for MySQL
  // ----------------------------------------
  // UserRole
  // ----------------------------------------
  // await prisma.userRole.deleteMany();
  // await prisma.userRole.createMany({ data: userRoleData });
  // await prisma.$executeRaw`ALTER TABLE UserRole AUTO_INCREMENT = 1`;
  // ----------------------------------------
  // User
  // ----------------------------------------
  // await prisma.user.deleteMany();
  // await prisma.user.createMany({ data: userData });
  // ========================================
  // Code for SQLite
  // ========================================
  // UserRole
  // ----------------------------------------
  // await prisma.userRole.deleteMany();
  // await prisma.userRole.createMany({ data: userRoleData });
  // SQLite automatically handles ID incrementation and does not require manual sequence reset
  // ----------------------------------------
  // User
  // ----------------------------------------
  // await prisma.user.deleteMany();
  // await prisma.user.createMany({ data: userData });
  // ========================================
  // Code for MongoDB
  // ----------------------------------------
  // UserRole
  // ----------------------------------------
  // await prisma.userRole.deleteMany();
  // await prisma.userRole.createMany({ data: userRoleData });
  // ----------------------------------------
  // User
  // ----------------------------------------
  // await prisma.user.deleteMany();
  // await prisma.user.createMany({ data: userData });
  // ========================================
}

main()
  .catch((e) => {
    throw e;
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
